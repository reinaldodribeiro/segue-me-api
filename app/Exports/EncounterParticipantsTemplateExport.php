<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EncounterParticipantsTemplateExport implements FromArray, WithColumnWidths, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Modelo de Encontristas';
    }

    public function headings(): array
    {
        return [
            'nome *',
            'tipo * (jovem|casal)',
            'nome_conjuge',
            'telefone',
            'email',
            'nascimento (DD/MM/AAAA)',
        ];
    }

    public function array(): array
    {
        return [
            // ── Jovens ──────────────────────────────────────────────────────
            ['Ana Beatriz Ferreira',         'jovem', '', '(11) 91234-0001', 'ana.ferreira@email.com',      '12/04/2001'],
            ['Carlos Eduardo Lima',          'jovem', '', '(11) 91234-0002', 'carlos.lima@email.com',       '08/09/1999'],
            ['Fernanda Oliveira',            'jovem', '', '(11) 91234-0003', 'fernanda.oli@email.com',      '22/11/2000'],
            ['Lucas Henrique Santos',        'jovem', '', '(11) 91234-0004', 'lucas.santos@email.com',      '03/06/1998'],
            ['Mariana Costa',                'jovem', '', '(11) 91234-0005', 'mariana.costa@email.com',     '17/02/2003'],
            ['Pedro Augusto Rocha',          'jovem', '', '(11) 91234-0006', 'pedro.rocha@email.com',       '30/07/2002'],
            ['Juliana Alves',                'jovem', '', '(11) 91234-0007', 'juliana.alves@email.com',     '14/01/2001'],
            ['Rafael Mendes',                'jovem', '', '(11) 91234-0008', 'rafael.mendes@email.com',     '05/10/1997'],
            ['Isabela Monteiro',             'jovem', '', '(11) 91234-0009', 'isabela.mont@email.com',      '28/08/2004'],
            ['Thiago Barbosa',               'jovem', '', '(11) 91234-0010', 'thiago.barb@email.com',       '11/05/2000'],
            ['Camila Rodrigues',             'jovem', '', '(11) 91234-0011', 'camila.rod@email.com',        '19/03/2002'],
            ['Gustavo Pereira',              'jovem', '', '(11) 91234-0012', 'gustavo.pereira@email.com',   '25/12/1999'],
            ['Larissa Nunes',                'jovem', '', '(11) 91234-0013', 'larissa.nunes@email.com',     '07/07/2001'],
            ['Bruno Carvalho',               'jovem', '', '(11) 91234-0014', 'bruno.carv@email.com',        '15/09/1998'],
            ['Natalia Souza',                'jovem', '', '(11) 91234-0015', 'natalia.sou@email.com',       '02/04/2003'],
            ['Felipe Nascimento',            'jovem', '', '(11) 91234-0016', 'felipe.nasc@email.com',       '20/06/2000'],
            ['Aline Teixeira',               'jovem', '', '(11) 91234-0017', 'aline.teix@email.com',        '09/11/2002'],
            ['Diego Fonseca',                'jovem', '', '(11) 91234-0018', 'diego.fons@email.com',        '31/01/1997'],
            ['Priscila Castro',              'jovem', '', '(11) 91234-0019', 'priscila.cas@email.com',      '18/08/2001'],
            ['Victor Gomes',                 'jovem', '', '(11) 91234-0020', 'victor.gomes@email.com',      '26/03/2004'],
            ['Bianca Freitas',               'jovem', '', '(11) 91234-0021', 'bianca.frei@email.com',       '13/07/2002'],
            ['Rodrigo Pinto',                'jovem', '', '(11) 91234-0022', 'rodrigo.pinto@email.com',     '04/02/2000'],
            ['Vanessa Araújo',               'jovem', '', '(11) 91234-0023', 'vanessa.arau@email.com',      '21/10/2003'],
            ['Mateus Correia',               'jovem', '', '(11) 91234-0024', 'mateus.corr@email.com',       '06/05/1999'],
            ['Sabrina Lima',                 'jovem', '', '(11) 91234-0025', 'sabrina.lima@email.com',      '29/09/2001'],
            ['João Victor Almeida',          'jovem', '', '(11) 91234-0026', 'joao.almeida@email.com',      '17/03/2003'],
            ['Letícia Melo',                 'jovem', '', '(11) 91234-0027', 'leticia.melo@email.com',      '24/06/2002'],
            ['Gabriel Cardoso',              'jovem', '', '(11) 91234-0028', 'gabriel.card@email.com',      '10/12/1999'],
            ['Patrícia Duarte',              'jovem', '', '(11) 91234-0029', 'patricia.dua@email.com',      '05/07/2001'],
            ['Henrique Batista',             'jovem', '', '(11) 91234-0030', 'henrique.bat@email.com',      '28/02/2000'],
            ['Monique Silveira',             'jovem', '', '(11) 91234-0031', 'monique.sil@email.com',       '14/11/2003'],
            ['André Tavares',                'jovem', '', '(11) 91234-0032', 'andre.tav@email.com',         '03/09/1998'],
            ['Caroline Macedo',              'jovem', '', '(11) 91234-0033', 'caroline.mac@email.com',      '19/04/2002'],
            ['Samuel Ribeiro',               'jovem', '', '(11) 91234-0034', 'samuel.rib@email.com',        '07/01/2001'],
            ['Tânia Borges',                 'jovem', '', '(11) 91234-0035', 'tania.bor@email.com',         '30/08/2004'],
            ['Leandro Campos',               'jovem', '', '(11) 91234-0036', 'leandro.camp@email.com',      '11/06/1997'],
            ['Érica Nogueira',               'jovem', '', '(11) 91234-0037', 'erica.nog@email.com',         '23/10/2000'],
            ['Maurício Leal',                'jovem', '', '(11) 91234-0038', 'mauricio.lea@email.com',      '15/03/2002'],
            ['Simone Pacheco',               'jovem', '', '(11) 91234-0039', 'simone.pac@email.com',        '08/12/2001'],
            ['Fábio Queiroz',                'jovem', '', '(11) 91234-0040', 'fabio.que@email.com',         '27/05/1999'],
            ['Débora Serrano',               'jovem', '', '(11) 91234-0041', 'debora.ser@email.com',        '16/09/2003'],
            ['Marcos Aurélio Vilas',         'jovem', '', '(11) 91234-0042', 'marcos.vilas@email.com',      '01/07/2000'],
            ['Renata Guimarães',             'jovem', '', '(11) 91234-0043', 'renata.gui@email.com',        '18/02/2002'],
            ['Cláudio Mendonça',             'jovem', '', '(11) 91234-0044', 'claudio.mend@email.com',      '09/10/1998'],
            ['Verônica Esteves',             'jovem', '', '(11) 91234-0045', 'veronica.est@email.com',      '22/04/2004'],
            // ── Casais ──────────────────────────────────────────────────────
            ['Roberto Andrade',              'casal', 'Silvia Andrade',   '(11) 91234-0046', 'roberto.and@email.com',    '14/03/1980'],
            ['Marcos Vieira',                'casal', 'Patricia Vieira',  '(11) 91234-0047', 'marcos.vie@email.com',     '22/06/1983'],
            ['Eduardo Dias',                 'casal', 'Renata Dias',      '(11) 91234-0048', 'eduardo.dias@email.com',   '07/11/1978'],
            ['André Moreira',                'casal', 'Claudia Moreira',  '(11) 91234-0049', 'andre.mor@email.com',      '18/08/1985'],
            ['Sérgio Ramos',                 'casal', 'Fernanda Ramos',   '(11) 91234-0050', 'sergio.ramos@email.com',   '03/04/1982'],
            ['Paulo Lopes',                  'casal', 'Maria Lopes',      '(11) 91234-0051', 'paulo.lopes@email.com',    '25/01/1975'],
            ['Leandro Alves',                'casal', 'Cristina Alves',   '(11) 91234-0052', 'leandro.alv@email.com',    '12/09/1987'],
            ['Fábio Neves',                  'casal', 'Juliana Neves',    '(11) 91234-0053', 'fabio.neves@email.com',    '30/07/1984'],
            ['Ricardo Costa',                'casal', 'Tatiana Costa',    '(11) 91234-0054', 'ricardo.cos@email.com',    '08/02/1990'],
            ['Henrique Souza',               'casal', 'Ana Paula Souza',  '(11) 91234-0055', 'henrique.sou@email.com',   '16/05/1986'],
            ['Alexandre Ferreira',           'casal', 'Lucia Ferreira',   '(11) 91234-0056', 'alex.ferr@email.com',      '27/10/1979'],
            ['Marcelo Barbosa',              'casal', 'Sandra Barbosa',   '(11) 91234-0057', 'marcelo.barb@email.com',   '11/03/1988'],
            ['Nelson Carvalho',              'casal', 'Denise Carvalho',  '(11) 91234-0058', 'nelson.carv@email.com',    '04/12/1981'],
            ['Gilberto Peixoto',             'casal', 'Rose Peixoto',     '(11) 91234-0059', 'gilberto.pei@email.com',   '19/06/1977'],
            ['Danilo Martins',               'casal', 'Viviane Martins',  '(11) 91234-0060', 'danilo.mart@email.com',    '23/08/1991'],
            ['Cássio Braga',                 'casal', 'Miriam Braga',     '(11) 91234-0061', 'cassio.braga@email.com',   '06/04/1984'],
            ['Fernando Assis',               'casal', 'Adriana Assis',    '(11) 91234-0062', 'fernando.ass@email.com',   '14/10/1979'],
            ['Rogério Cunha',                'casal', 'Solange Cunha',    '(11) 91234-0063', 'rogerio.cun@email.com',    '31/07/1986'],
            ['Cledson Mota',                 'casal', 'Eliane Mota',      '(11) 91234-0064', 'cledson.mot@email.com',    '09/02/1982'],
            ['Antônio Cavalcante',           'casal', 'Neusa Cavalcante', '(11) 91234-0065', 'antonio.cav@email.com',    '17/11/1976'],
            ['Davi Rezende',                 'casal', 'Glória Rezende',   '(11) 91234-0066', 'davi.rez@email.com',       '25/05/1989'],
            ['Osmar Prado',                  'casal', 'Lúcia Prado',      '(11) 91234-0067', 'osmar.prad@email.com',     '12/08/1983'],
            ['Adilson Freire',               'casal', 'Rosana Freire',    '(11) 91234-0068', 'adilson.fre@email.com',    '20/03/1980'],
            ['Wellington Paiva',             'casal', 'Ivone Paiva',      '(11) 91234-0069', 'wellington.p@email.com',   '03/09/1987'],
            ['Cristiano Lago',               'casal', 'Beatriz Lago',     '(11) 91234-0070', 'cristiano.lag@email.com',  '29/01/1985'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 22,
            'C' => 25,
            'D' => 18,
            'E' => 32,
            'F' => 24,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->array()) + 1;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF6D28D9']],
            ],
            "A2:F{$lastRow}" => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF5F3FF']],
            ],
        ];
    }
}
