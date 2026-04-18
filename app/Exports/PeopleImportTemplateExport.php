<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PeopleImportTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Modelo de Importação';
    }

    public function headings(): array
    {
        return [
            'nome *',
            'tipo * (jovem|casal)',
            'nome_conjuge (opcional, para casais)',
            'data_nascimento (DD/MM/AAAA, opcional)',
            'data_nascimento_conjuge (DD/MM/AAAA, opcional)',
            'data_casamento (DD/MM/AAAA, opcional)',
            'telefone (opcional)',
            'email (opcional)',
            'habilidades (opcional, separadas por vírgula)',
            'ano_encontro (opcional)',
            'observacoes (opcional)',
        ];
    }

    public function array(): array
    {
        return [
            // Jovens
            ['Ana Beatriz Ferreira',       'jovem', '', '12/04/2001', '', '', '(11) 91234-0001', 'ana.ferreira@email.com',   'música, canto, violão',               '2023', ''],
            ['Carlos Eduardo Lima',        'jovem', '', '08/09/1999', '', '', '(11) 91234-0002', 'carlos.lima@email.com',    'audiovisual, fotografia, edição',     '2022', 'Responsável pelo canal da paróquia'],
            ['Fernanda Oliveira',          'jovem', '', '22/11/2000', '', '', '(11) 91234-0003', 'fernanda.oli@email.com',   'teatro, dramatização, dança',         '',     ''],
            ['Lucas Henrique Santos',      'jovem', '', '03/06/1998', '', '', '(11) 91234-0004', 'lucas.santos@email.com',   'pregação, catequese',                 '2020', 'Ministrante de encontros anteriores'],
            ['Mariana Costa',              'jovem', '', '17/02/2003', '', '', '(11) 91234-0005', 'mariana.costa@email.com',  'decoração, artesanato',               '',     ''],
            ['Pedro Augusto Rocha',        'jovem', '', '30/07/2002', '', '', '(11) 91234-0006', 'pedro.rocha@email.com',    'esporte, animação, liderança',        '2021', 'Coordenador da pastoral jovem'],
            ['Juliana Alves',              'jovem', '', '14/01/2001', '', '', '(11) 91234-0007', 'juliana.alves@email.com',  'canto, liturgia',                     '2023', ''],
            ['Rafael Mendes',              'jovem', '', '05/10/1997', '', '', '(11) 91234-0008', 'rafael.mendes@email.com',  'som, audiovisual',                    '2019', 'Técnico de som voluntário há 3 anos'],
            ['Isabela Monteiro',           'jovem', '', '28/08/2004', '', '', '(11) 91234-0009', 'isabela.mont@email.com',   'dança, teatro',                       '',     'Primeiro encontro'],
            ['Thiago Barbosa',             'jovem', '', '11/05/2000', '', '', '(11) 91234-0010', 'thiago.barb@email.com',    'comunicação, liderança, pregação',    '2021', ''],
            ['Camila Rodrigues',           'jovem', '', '19/03/2002', '', '', '(11) 91234-0011', 'camila.rod@email.com',     'decoração, artesanato, culinária',    '',     ''],
            ['Gustavo Pereira',            'jovem', '', '25/12/1999', '', '', '(11) 91234-0012', 'gustavo.pereira@email.com', 'violão, teclado, canto',              '2020', 'Lidera grupo de louvor'],
            ['Larissa Nunes',              'jovem', '', '07/07/2001', '', '', '(11) 91234-0013', 'larissa.nunes@email.com',  'catequese, ensino',                   '',     ''],
            ['Bruno Carvalho',             'jovem', '', '15/09/1998', '', '', '(11) 91234-0014', 'bruno.carv@email.com',     'esporte, animação',                   '2022', ''],
            ['Natalia Souza',              'jovem', '', '02/04/2003', '', '', '(11) 91234-0015', 'natalia.sou@email.com',    'liturgia, altar',                     '',     'Coroinha há 4 anos'],
            ['Felipe Nascimento',          'jovem', '', '20/06/2000', '', '', '(11) 91234-0016', 'felipe.nasc@email.com',    'comunicação, redes sociais',          '2021', 'Criador de conteúdo da pastoral'],
            ['Aline Teixeira',             'jovem', '', '09/11/2002', '', '', '(11) 91234-0017', 'aline.teix@email.com',     'música, flauta',                      '',     ''],
            ['Diego Fonseca',              'jovem', '', '31/01/1997', '', '', '(11) 91234-0018', 'diego.fons@email.com',     'pregação, testemunho, liderança',     '2018', 'Participou de 5 encontros'],
            ['Priscila Castro',            'jovem', '', '18/08/2001', '', '', '(11) 91234-0019', 'priscila.cas@email.com',   'culinária, organização',              '2023', ''],
            ['Victor Gomes',               'jovem', '', '26/03/2004', '', '', '(11) 91234-0020', 'victor.gomes@email.com',   'teatro, humor',                       '',     'Primeiro encontro'],
            ['Bianca Freitas',             'jovem', '', '13/07/2002', '', '', '(11) 91234-0021', 'bianca.frei@email.com',    'canto, violão, música',               '2022', ''],
            ['Rodrigo Pinto',              'jovem', '', '04/02/2000', '', '', '(11) 91234-0022', 'rodrigo.pinto@email.com',  'audiovisual, fotografia',             '',     ''],
            ['Vanessa Araújo',             'jovem', '', '21/10/2003', '', '', '(11) 91234-0023', 'vanessa.arau@email.com',   'dança, animação',                     '',     ''],
            ['Mateus Correia',             'jovem', '', '06/05/1999', '', '', '(11) 91234-0024', 'mateus.corr@email.com',    'liderança, pregação, catequese',      '2019', 'Monitor da turma de pré-crisma'],
            ['Sabrina Lima',               'jovem', '', '29/09/2001', '', '', '(11) 91234-0025', 'sabrina.lima@email.com',   'decoração, liturgia',                 '2023', ''],
            // Casais
            ['Roberto e Silvia Andrade',   'casal', 'Silvia Andrade',  '14/03/1980', '22/07/1982', '15/05/2004', '(11) 91234-0026', 'roberto.and@email.com',    'pregação, testemunho, liderança',     '2016', 'Casal âncora há 8 anos'],
            ['Marcos e Patricia Vieira',   'casal', 'Patricia Vieira', '22/06/1983', '10/01/1985', '20/11/2008', '(11) 91234-0027', 'marcos.vie@email.com',     'culinária, organização, hospitalidade', '2018', 'Responsáveis pela recepção'],
            ['Eduardo e Renata Dias',      'casal', 'Renata Dias',     '07/11/1978', '03/04/1980', '12/03/2002', '(11) 91234-0028', 'eduardo.dias@email.com',   'catequese, ensino, pregação',         '2014', 'Catequistas há 12 anos'],
            ['André e Claudia Moreira',    'casal', 'Claudia Moreira', '18/08/1985', '25/12/1987', '30/06/2010', '(11) 91234-0029', 'andre.mor@email.com',      'música, violão, canto',               '2019', 'Lideram grupo de louvor do casal'],
            ['Sérgio e Fernanda Ramos',    'casal', 'Fernanda Ramos',  '03/04/1982', '15/09/1984', '08/02/2007', '(11) 91234-0030', 'sergio.ramos@email.com',   'audiovisual, comunicação',            '2020', 'Produzem vídeos da paróquia'],
            ['Paulo e Maria Lopes',        'casal', 'Maria Lopes',     '25/01/1975', '17/06/1977', '25/01/2000', '(11) 91234-0031', 'paulo.lopes@email.com',    'liturgia, altar, liderança',          '2012', 'Ministros da eucaristia'],
            ['Leandro e Cristina Alves',   'casal', 'Cristina Alves',  '12/09/1987', '08/11/1989', '14/07/2012', '(11) 91234-0032', 'leandro.alv@email.com',    'decoração, artesanato',               '',     ''],
            ['Fábio e Juliana Neves',      'casal', 'Juliana Neves',   '30/07/1984', '05/03/1986', '22/09/2009', '(11) 91234-0033', 'fabio.neves@email.com',    'esporte, animação, liderança',        '2017', 'Coordenadores do grupo de casais'],
            ['Ricardo e Tatiana Costa',    'casal', 'Tatiana Costa',   '08/02/1990', '12/08/1992', '18/04/2015', '(11) 91234-0034', 'ricardo.cos@email.com',    'testemunho, pregação',                '2024', 'Segundo encontro como casal'],
            ['Henrique e Ana Paula Souza', 'casal', 'Ana Paula Souza', '16/05/1986', '29/10/1988', '03/12/2011', '(11) 91234-0035', 'henrique.sou@email.com',   'culinária, hospitalidade',            '',     ''],
            ['Alexandre e Lucia Ferreira', 'casal', 'Lucia Ferreira',  '27/10/1979', '14/02/1981', '20/08/2003', '(11) 91234-0036', 'alex.ferr@email.com',      'catequese, ensino, comunicação',      '2015', 'Formadores do encontro de noivos'],
            ['Marcelo e Sandra Barbosa',   'casal', 'Sandra Barbosa',  '11/03/1988', '06/07/1990', '28/01/2013', '(11) 91234-0037', 'marcelo.barb@email.com',   'música, teclado, canto',              '',     ''],
            ['Nelson e Denise Carvalho',   'casal', 'Denise Carvalho', '04/12/1981', '19/05/1983', '10/10/2006', '(11) 91234-0038', 'nelson.carv@email.com',    'liderança, organização',              '2025', 'Primeiro encontro como casal'],
            ['Gilberto e Rose Peixoto',    'casal', 'Rose Peixoto',    '19/06/1977', '23/03/1979', '07/07/2001', '(11) 91234-0039', 'gilberto.pei@email.com',   'pregação, testemunho, liturgia',      '2013', 'Veteranos — participaram de 9 encontros'],
            ['Danilo e Viviane Martins',   'casal', 'Viviane Martins', '23/08/1991', '11/11/1993', '15/06/2016', '(11) 91234-0040', 'danilo.mart@email.com',    'decoração, artesanato, dança',        '2022', ''],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->array()) + 1;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2E6DA4']],
            ],
            "A2:K{$lastRow}" => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF0F4FA']],
            ],
        ];
    }
}
