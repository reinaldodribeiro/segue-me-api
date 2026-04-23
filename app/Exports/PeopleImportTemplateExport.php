<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PeopleImportTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly string $type = 'youth') {}

    public function title(): string
    {
        return $this->type === 'couple' ? 'Modelo - Casais' : 'Modelo - Jovens';
    }

    public function headings(): array
    {
        if ($this->type === 'couple') {
            return [
                'nome',
                'tipo',
                'apelido',
                'data_nascimento',
                'naturalidade',
                'endereco',
                'telefone',
                'email',
                'movimento_igreja',
                'data_recebimento',
                'ano_encontro',
                'detalhes_encontro',
                'habilidades',
                'observacoes',
                'nome_conjuge',
                'apelido_conjuge',
                'data_nascimento_conjuge',
                'naturalidade_conjuge',
                'email_conjuge',
                'telefones_conjuge',
                'data_casamento',
                'telefones_residencial',
            ];
        }

        // Youth (24 columns)
        return [
            'nome',
            'tipo',
            'apelido',
            'data_nascimento',
            'naturalidade',
            'endereco',
            'telefone',
            'email',
            'movimento_igreja',
            'data_recebimento',
            'ano_encontro',
            'detalhes_encontro',
            'habilidades',
            'observacoes',
            'nome_pai',
            'nome_mae',
            'nivel_educacao',
            'status_educacao',
            'curso',
            'instituicao',
            'sacramentos',
            'disponibilidade_horario',
            'instrumentos_musicais',
            'pregacoes_testemunhos',
        ];
    }

    public function array(): array
    {
        if ($this->type === 'couple') {
            return $this->coupleRows();
        }

        return $this->youthRows();
    }

    private function youthRows(): array
    {
        // 24 columns: nome, tipo, apelido, data_nascimento, naturalidade, endereco, telefone,
        // email, movimento_igreja, data_recebimento, ano_encontro, detalhes_encontro,
        // habilidades, observacoes, nome_pai, nome_mae, nivel_educacao, status_educacao,
        // curso, instituicao, sacramentos, disponibilidade_horario, instrumentos_musicais,
        // pregacoes_testemunhos
        return [
            ['Ana Beatriz Ferreira',  'jovem', 'Aninha',  '12/04/2001', 'São Paulo/SP', 'Rua das Flores, 123 - Vila Nova, SP', '(11) 91234-0001', 'ana.ferreira@email.com',   'Renovação Carismática', '15/03/2023', '2023', 'Encontrou o Senhor através de um retiro paroquial.',       'música, canto, violão',            '',                             'José Ferreira',    'Maria Ferreira',    'Ensino Superior',  'Cursando',   'Letras',         'USP',             'batismo, eucaristia',       'manhãs e noites',    'violão',           ''],
            ['Carlos Eduardo Lima',   'jovem', 'Carlão',  '08/09/1999', 'Campinas/SP',  'Av. Brasil, 456 - Centro, Campinas/SP', '(11) 91234-0002', 'carlos.lima@email.com',    'Encontro de Jovens',   '10/06/2022', '2022', 'Veio pelo convite de um amigo do grupo de jovens.',        'audiovisual, fotografia, edição',  'Responsável pelo canal da paróquia', 'Paulo Lima',    'Ana Lima',          'Ensino Superior',  'Cursando',   'Cinema',         'Unicamp',         'batismo, eucaristia, crisma', 'noites',            '',             'Palestra sobre vocação no encontro de 2022'],
            ['Fernanda Oliveira',     'jovem', 'Fer',     '22/11/2000', 'Santos/SP',    'Rua do Porto, 78 - Gonzaga, Santos/SP', '(11) 91234-0003', 'fernanda.oli@email.com',   'Movimento Shalom',     '',           '',     '',                                                         'teatro, dramatização, dança',      '',                             'Roberto Oliveira', 'Clara Oliveira',    'Ensino Médio',     'Completo',   '',               '',                'batismo, eucaristia',       'fins de semana',     '',             ''],
            ['Lucas Henrique Santos', 'jovem', 'Lucas',   '03/06/1998', 'Sorocaba/SP',  'Rua XV de Novembro, 200 - Centro, Sorocaba/SP', '(11) 91234-0004', 'lucas.santos@email.com', 'Renovação Carismática', '20/01/2020', '2020', 'Ministrante de encontros anteriores, líder de célula.', 'pregação, catequese',              'Ministrante de encontros anteriores', 'Antônio Santos', 'Regina Santos',    'Ensino Superior',  'Completo',   'Teologia',       'Faculdade Sagrado Coração', 'batismo, eucaristia, crisma', 'manhãs',            '',             'Três pregações nos encontros de 2020 e 2022'],
            ['Mariana Costa',         'jovem', 'Mari',    '17/02/2003', 'Guarulhos/SP', 'Rua Sete de Setembro, 312 - Jardim Esperança, GRU/SP', '(11) 91234-0005', 'mariana.costa@email.com', 'Pastoral Jovem', '', '', '', 'decoração, artesanato', '', 'Carlos Costa', 'Patrícia Costa', 'Ensino Médio', 'Cursando', '', '', 'batismo', 'noites', '', ''],
            ['Pedro Augusto Rocha',   'jovem', 'Pedro',   '30/07/2002', 'São Paulo/SP', 'Rua Augusta, 890 - Consolação, SP', '(11) 91234-0006', 'pedro.rocha@email.com',    'Encontro de Jovens',   '05/08/2021', '2021', 'Coordenador da pastoral jovem da paróquia.',              'esporte, animação, liderança',     'Coordenador da pastoral jovem', 'Fernando Rocha', 'Luciana Rocha',    'Ensino Superior',  'Cursando',   'Administração',  'FGV',             'batismo, eucaristia',       'manhãs e tardes',    '',             ''],
            ['Juliana Alves',         'jovem', 'Juli',    '14/01/2001', 'Mauá/SP',      'Av. Capitão João, 150 - Centro, Mauá/SP', '(11) 91234-0007', 'juliana.alves@email.com', 'Renovação Carismática', '12/04/2023', '2023', '', 'canto, liturgia', '', 'Marcelo Alves', 'Sandra Alves', 'Ensino Superior', 'Cursando', 'Música', 'UNESP', 'batismo, eucaristia, crisma', 'noites', 'flauta, teclado', ''],
            ['Rafael Mendes',         'jovem', 'Rafa',    '05/10/1997', 'São Paulo/SP', 'Rua Vergueiro, 1200 - Vila Mariana, SP', '(11) 91234-0008', 'rafael.mendes@email.com',  'Comunidade Canção Nova', '18/07/2019', '2019', 'Técnico de som voluntário há 3 anos.',                 'som, audiovisual',                 'Técnico de som voluntário há 3 anos', 'José Mendes', 'Fátima Mendes',    'Ensino Superior',  'Completo',   'Engenharia Elétrica', 'POLI-USP',  'batismo, eucaristia, crisma', 'noites',            '',             'Testemunho no encontro de 2019'],
        ];
    }

    private function coupleRows(): array
    {
        // 22 columns: nome, tipo, apelido, data_nascimento, naturalidade, endereco, telefone,
        // email, movimento_igreja, data_recebimento, ano_encontro, detalhes_encontro,
        // habilidades, observacoes, nome_conjuge, apelido_conjuge, data_nascimento_conjuge,
        // naturalidade_conjuge, email_conjuge, telefones_conjuge, data_casamento,
        // telefones_residencial
        return [
            ['Roberto Andrade',    'casal', 'Roberto', '14/03/1980', 'São Paulo/SP',    'Rua das Palmeiras, 45 - Vila Madalena, SP',       '(11) 91234-0026', 'roberto.and@email.com',    'Renovação Carismática', '10/03/2016', '2016', 'Casal âncora há 8 anos, formadores do encontro.',        'pregação, testemunho, liderança',      'Casal âncora há 8 anos',              'Silvia Andrade',    'Sil',       '22/07/1982', 'Guarulhos/SP',    'silvia.and@email.com',     '(11) 91234-0126', '15/05/2004', '(11) 3456-0001'],
            ['Marcos Vieira',      'casal', 'Marcos',  '22/06/1983', 'Campinas/SP',     'Av. Ipiranga, 300 - Centro, Campinas/SP',         '(11) 91234-0027', 'marcos.vie@email.com',     'Encontro de Casais',    '08/09/2018', '2018', 'Responsáveis pela recepção e acolhida dos participantes.', 'culinária, organização, hospitalidade', 'Responsáveis pela recepção',          'Patricia Vieira',   'Pat',       '10/01/1985', 'São Paulo/SP',    'patricia.vie@email.com',   '(11) 91234-0127', '20/11/2008', '(11) 3456-0002'],
            ['Eduardo Dias',       'casal', 'Eduardo', '07/11/1978', 'Santos/SP',       'Rua XV de Novembro, 88 - Centro, Santos/SP',      '(11) 91234-0028', 'eduardo.dias@email.com',   'Renovação Carismática', '15/02/2014', '2014', 'Catequistas há 12 anos, formadores do encontro de noivos.', 'catequese, ensino, pregação',         'Catequistas há 12 anos',              'Renata Dias',       'Rê',        '03/04/1980', 'São Paulo/SP',    'renata.dias@email.com',    '(11) 91234-0128', '12/03/2002', '(11) 3456-0003'],
            ['André Moreira',      'casal', 'André',   '18/08/1985', 'Sorocaba/SP',     'Rua Boa Vista, 520 - Jardim Paulistano, SP',      '(11) 91234-0029', 'andre.mor@email.com',      'Pastoral Familiar',     '20/04/2019', '2019', 'Lideram grupo de louvor de casais na paróquia.',          'música, violão, canto',               'Lideram grupo de louvor do casal',    'Claudia Moreira',   'Clau',      '25/12/1987', 'Mauá/SP',         'claudia.mor@email.com',    '(11) 91234-0129', '30/06/2010', '(11) 3456-0004'],
            ['Sérgio Ramos',       'casal', 'Sérgio',  '03/04/1982', 'São Paulo/SP',    'Av. Santo Amaro, 760 - Vila Nova Conceição, SP', '(11) 91234-0030', 'sergio.ramos@email.com',   'Comunidade Canção Nova', '11/11/2020', '2020', 'Produzem vídeos e conteúdo audiovisual para a paróquia.', 'audiovisual, comunicação',            'Produzem vídeos da paróquia',         'Fernanda Ramos',    'Fer',       '15/09/1984', 'Guarulhos/SP',    'fernanda.ramos@email.com', '(11) 91234-0130', '08/02/2007', '(11) 3456-0005'],
            ['Paulo Lopes',        'casal', 'Paulo',   '25/01/1975', 'São Paulo/SP',    'Rua da Consolação, 1500 - Consolação, SP',        '(11) 91234-0031', 'paulo.lopes@email.com',    'Renovação Carismática', '30/06/2012', '2012', 'Ministros da eucaristia e líderes da comunidade.',       'liturgia, altar, liderança',          'Ministros da eucaristia',             'Maria Lopes',       'Maria',     '17/06/1977', 'Santos/SP',       'maria.lopes@email.com',    '(11) 91234-0131', '25/01/2000', '(11) 3456-0006'],
            ['Leandro Alves',      'casal', 'Leandro', '12/09/1987', 'Campinas/SP',     'Rua Barão de Limeira, 200 - Campos Elíseos, SP', '(11) 91234-0032', 'leandro.alv@email.com',    'Pastoral Familiar',     '',           '',     '',                                                        'decoração, artesanato',               '',                                    'Cristina Alves',    'Cris',      '08/11/1989', 'Sorocaba/SP',     'cristina.alv@email.com',   '(11) 91234-0132', '14/07/2012', '(11) 3456-0007'],
            ['Fábio Neves',        'casal', 'Fábio',   '30/07/1984', 'São Paulo/SP',    'Av. Rebouças, 1100 - Pinheiros, SP',              '(11) 91234-0033', 'fabio.neves@email.com',    'Encontro de Casais',    '22/09/2017', '2017', 'Coordenadores do grupo de casais da paróquia.',           'esporte, animação, liderança',        'Coordenadores do grupo de casais',    'Juliana Neves',     'Juli',      '05/03/1986', 'Guarulhos/SP',    'juliana.neves@email.com',  '(11) 91234-0133', '22/09/2009', '(11) 3456-0008'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->array()) + 1;
        $lastCol = $this->type === 'couple' ? 'V' : 'X';

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2E6DA4']],
            ],
            "A2:{$lastCol}{$lastRow}" => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF0F4FA']],
            ],
        ];
    }
}
