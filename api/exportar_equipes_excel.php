<?php
require '../includes/db_connect.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Planilha
$spreadsheet = new Spreadsheet();

// --- ABA 1: Equipes ---
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Equipes');

$sheet1->fromArray(['ID', 'Descrição', 'Status', 'Filial', 'Processo', 'Atividade', 'Coordenador', 'Supervisor', 'Líder'], NULL, 'A1');

$sql = "
    SELECT 
        e.id, e.descricao, e.status,
        f.nome AS filial,
        p.titulo AS processo,
        a.titulo AS atividade,
        c1.nome AS coordenador,
        c2.nome AS supervisor,
        c3.nome AS lider
    FROM equipes e
    INNER JOIN atividades a ON e.atividade_id = a.id
    INNER JOIN processos p ON a.processo_id = p.id
    INNER JOIN filiais f ON p.filial_id = f.id
    LEFT JOIN colaboradores c1 ON e.coordenador_id = c1.id
    LEFT JOIN colaboradores c2 ON e.supervisor_id = c2.id
    LEFT JOIN colaboradores c3 ON e.lider_id = c3.id
    ORDER BY e.id DESC
";

$result = $conn->query($sql);
$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $sheet1->fromArray(array_values($row), NULL, 'A' . $rowNum++);
}

// --- ABA 2: Colaboradores por Equipe ---
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Colaboradores');

$sheet2->fromArray(['Equipe ID', 'Equipe Descrição', 'Colaborador', 'Cargo'], NULL, 'A1');

$sql2 = "
    SELECT 
        ec.equipe_id,
        e.descricao AS equipe,
        c.nome AS colaborador,
        ec.cargo
    FROM equipe_colaboradores ec
    INNER JOIN equipes e ON ec.equipe_id = e.id
    INNER JOIN colaboradores c ON ec.colaborador_id = c.id
    ORDER BY ec.equipe_id
";

$result2 = $conn->query($sql2);
$rowNum = 2;
while ($row = $result2->fetch_assoc()) {
    $sheet2->fromArray(array_values($row), NULL, 'A' . $rowNum++);
}

// Exportar
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="equipes.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
