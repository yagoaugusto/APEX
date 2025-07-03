<?php
// Define o caminho completo para o arquivo de template
$file_path = __DIR__ . '/../assets/templates/colaboradores_template.xlsx';
$file_name = 'colaboradores_template.xlsx';

// Verifica se o arquivo existe
if (file_exists($file_path)) {
    // Define os cabeçalhos HTTP para forçar o download e indicar o tipo de arquivo
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // MIME type para .xlsx
    header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    // Limpa o buffer de saída e lê o arquivo
    ob_clean();
    flush();
    readfile($file_path);
    exit;
} else {
    // Se o arquivo não for encontrado, exibe uma mensagem de erro
    http_response_code(404);
    echo "Arquivo de template não encontrado.";
    exit;
}
?>