<?php

namespace App\Domain\AI\Prompts;

class ExtractFichaPrompt
{
    public static function build(): string
    {
        return <<<'PROMPT'
        Analise esta ficha de inscrição de encontro paroquial e extraia as informações em JSON.
        Retorne SOMENTE o JSON, sem texto adicional, sem markdown, sem blocos de código.

        Primeiro identifique o tipo de ficha: "youth" (Jovem) ou "couple" (Casal).

        Para ficha de JOVEM, extraia:
        {
          "type": "youth",
          "name": "string — nome completo",
          "nickname": "string|null — apelido",
          "address": "string|null — endereço",
          "email": "string|null",
          "phones": ["string"] — lista de telefones (até 4),
          "birth_date": "YYYY-MM-DD|null — data de nascimento",
          "birthplace": "string|null — naturalidade",
          "father_name": "string|null — nome do pai",
          "mother_name": "string|null — nome da mãe",
          "education_level": "string|null — escolaridade",
          "education_status": "string|null — situação: Cursando, Concluído ou Trancado",
          "course": "string|null — curso",
          "institution": "string|null — instituição de ensino",
          "sacraments": ["string"] — sacramentos recebidos: batismo, eucaristia, crisma,
          "church_movement": "string|null — movimento ou pastoral da Igreja que participa",
          "available_schedule": "string|null — horário disponível para atividades do SEGUE-ME",
          "encounter_details": "string|null — qual Encontro de Jovens com Cristo fez (número, paróquia e ano)",
          "encounter_year": "number|null — ano do encontro (se identificável)",
          "musical_instruments": "string|null — instrumentos musicais que toca",
          "talks_testimony": "string|null — palestras ou testemunhos que já ministrou",
          "received_at": "YYYY-MM-DD|null — data em que a ficha foi recebida",
          "skills": ["string"] — habilidades identificadas no texto,
          "notes": "string|null — observações adicionais"
        }

        Para ficha de CASAL, extraia:
        {
          "type": "couple",
          "name": "string — nome completo dele (ELE)",
          "partner_name": "string — nome completo dela (ELA)",
          "nickname": "string|null — apelido dele",
          "partner_nickname": "string|null — apelido dela",
          "birth_date": "YYYY-MM-DD|null — nascimento dele",
          "partner_birth_date": "YYYY-MM-DD|null — nascimento dela",
          "birthplace": "string|null — naturalidade dele",
          "partner_birthplace": "string|null — naturalidade dela",
          "email": "string|null — email dele",
          "partner_email": "string|null — email dela",
          "phones": ["string"] — telefones dele (até 2),
          "partner_phones": ["string"] — telefones dela (até 2),
          "address": "string|null — endereço residencial",
          "home_phones": ["string"] — telefones residenciais (até 2),
          "church_movement": "string|null — movimento ou pastoral da Igreja que participam",
          "encounter_details": "string|null — qual Encontro de Casais com Cristo (ECC) fizeram (número, paróquia e ano)",
          "encounter_year": "number|null — ano do encontro (se identificável)",
          "received_at": "YYYY-MM-DD|null — data em que a ficha foi recebida",
          "skills": ["string"] — habilidades identificadas no texto,
          "notes": "string|null — observações adicionais incluindo encontros ECC e SEGUE-ME que já trabalharam"
        }

        Se um campo não for encontrado ou não se aplicar, retorne null (ou array vazio para listas).
        PROMPT;
    }
}
