<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

// Inclui o autoloader do Composer para carregar a PhpSpreadsheet
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

$response = ['success' => false, 'message' => ''];
$imported_count = 0;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Nenhum arquivo enviado ou erro no upload.';
        echo json_encode($response);
        exit();
    }

    $file_tmp_path = $_FILES['excel_file']['tmp_name'];
    $file_name = $_FILES['excel_file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Validar tipo de arquivo
    $allowed_exts = ['xls', 'xlsx'];
    if (!in_array($file_ext, $allowed_exts)) {
        $response['message'] = 'Tipo de arquivo inválido. Apenas .xls ou .xlsx são permitidos.';
        echo json_encode($response);
        exit();
    }

    try {
        // Carregar o arquivo Excel
        $spreadsheet = IOFactory::load($file_tmp_path);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        // Mapear cabeçalhos das colunas para nomes de campo do banco de dados
        $header = [];
        foreach ($worksheet->getRowIterator(1, 1) as $row) { // Apenas a primeira linha
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Isso garante que iteramos sobre todas as células, mesmo as vazias
            foreach ($cellIterator as $cell) {
                $header[] = strtolower(trim($cell->getValue()));
            }
        }

        // Mapeamento esperado de colunas
        $expected_columns = [
            'nome', 'status', 'filial_id', 'cargo', 'cpf', 'matricula'
        ];

        // Verificar se todos os cabeçalhos esperados estão presentes
        if (count(array_intersect($expected_columns, $header)) !== count($expected_columns)) {
            $response['message'] = 'O arquivo Excel não possui todos os cabeçalhos obrigatórios: ' . implode(', ', $expected_columns);
            echo json_encode($response);
            exit();
        }

        // Preparar statement para inserção/atualização
        $stmt_insert = $conn->prepare("INSERT INTO colaboradores (nome, status, filial_id, cargo, cpf, matricula) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_update = $conn->prepare("UPDATE colaboradores SET nome = ?, status = ?, filial_id = ?, cargo = ?, cpf = ? WHERE matricula = ?");

        // Iterar sobre as linhas, começando da segunda (após o cabeçalho)
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];
            foreach ($worksheet->getRowIterator($row, $row) as $r) {
                $cellIterator = $r->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
            }

            // Mapear dados da linha para os nomes de campo
            $colaborador_data = [];
            foreach ($expected_columns as $index => $col_name) {
                $colaborador_data[$col_name] = $rowData[$index] ?? null;
            }

            // Validação e processamento de dados da linha
            $nome = trim($colaborador_data['nome']);
            $status = strtolower(trim($colaborador_data['status']));
            $filial_id = (int)$colaborador_data['filial_id'];
            $cargo = trim($colaborador_data['cargo']);
            $cpf = preg_replace('/[^0-9]/', '', trim($colaborador_data['cpf']));
            $matricula = trim($colaborador_data['matricula']);

            $row_errors = [];

            if (empty($nome) || empty($status) || empty($filial_id) || empty($cargo) || empty($cpf) || empty($matricula)) {
                $row_errors[] = 'Campos obrigatórios vazios.';
            }
            if (!in_array($status, ['ativo', 'ferias', 'desligado', 'afastado'])) {
                $row_errors[] = 'Status inválido. Use: ativo, ferias, desligado, afastado.';
            }
            if (strlen($cpf) != 11) {
                $row_errors[] = 'CPF inválido. Deve conter 11 dígitos.';
            }
            // Adicione mais validações conforme necessário (ex: filial_id existe no banco)

            if (empty($row_errors)) {
                // Checar se a matrícula já existe para decidir entre INSERT e UPDATE
                $stmt_check_matricula = $conn->prepare("SELECT id FROM colaboradores WHERE matricula = ?");
                $stmt_check_matricula->bind_param("s", $matricula);
                $stmt_check_matricula->execute();
                $check_result = $stmt_check_matricula->get_result();

                if ($check_result->num_rows > 0) {
                    // Matrícula existe, fazer UPDATE
                    $stmt_update->bind_param("ssisss", $nome, $status, $filial_id, $cargo, $cpf, $matricula);
                    if (!$stmt_update->execute()) {
                        $row_errors[] = 'Erro ao atualizar colaborador (Matrícula: ' . $matricula . '): ' . $stmt_update->error;
                    }
                } else {
                    // Matrícula não existe, fazer INSERT
                    $stmt_insert->bind_param("ssisss", $nome, $status, $filial_id, $cargo, $cpf, $matricula);
                    if (!$stmt_insert->execute()) {
                        $row_errors[] = 'Erro ao inserir colaborador (Matrícula: ' . $matricula . '): ' . $stmt_insert->error;
                    }
                }
                $stmt_check_matricula->close();
            }

            if (empty($row_errors)) {
                $imported_count++;
            } else {
                $errors[] = 'Linha ' . $row . ': ' . implode('; ', $row_errors);
            }
        }

        $stmt_insert->close();
        $stmt_update->close();

        $response['success'] = true;
        $response['message'] = 'Importação concluída. ' . $imported_count . ' colaboradores processados com sucesso.';
        if (!empty($errors)) {
            $response['message'] .= ' Erros encontrados: ' . implode("\n", $errors);
            $response['errors'] = $errors;
            $response['success'] = false; // Se houver erros, a operação geral não é 100% sucesso
        }

    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        $response['message'] = 'Erro ao ler o arquivo Excel: ' . $e->getMessage();
    } catch (Exception $e) {
        $response['message'] = 'Erro inesperado: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Método de requisição inválido.';
}

$conn->close();
echo json_encode($response);
?>