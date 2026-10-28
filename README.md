# AG Apps — Prospecção comercial (MVP)

Aplicação PHP 8 + PDO/MySQL + Bootstrap 5 + jQuery. Pesquisa empresas reais pela Google Places API (New), solicita até 10 resultados por página e aceita `nextPageToken`. Inclui deduplicação por `provider_place_id`, cadastro interno e histórico de contato.

## Requisitos
- PHP 8.1+ com extensões `pdo_mysql`, `curl`, `mbstring` e `json`.
- MySQL 8+.
- Projeto Google Cloud com Places API (New) habilitada, chave de API restrita ao servidor e faturamento configurado. O uso da API pode gerar cobrança.

## Instalação
1. Crie o banco importando `sql/schema.sql`.
2. Copie `config/config.example.php` para `config/config.php` e preencha MySQL e `google_places_api_key`.
3. Configure o servidor web para usar `public/` como DocumentRoot. Não exponha `config/`, `src/` ou `sql/` pela web.
4. Garanta que `config/config.php` não seja versionado nem publicado.
5. Acesse `public/index.php`.

Para teste local: `php -S 127.0.0.1:8080 -t public` a partir da pasta do projeto.

## Comportamento
- Filtros: estado, cidade, bairro opcional, segmento e prioridade.
- A API Places recebe `pageSize=10`; o botão “Carregar mais” usa o token retornado pela fonte. A API não garante 10 resultados para toda consulta.
- A deduplicação da pesquisa vale para a sessão atual; o banco usa índice único por provedor + ID para evitar duplicatas salvas.
- “Sem site informado” significa apenas que a fonte não retornou um site. A classificação de site com problema requer verificação manual nesta versão.
- O resultado da pesquisa não é persistido automaticamente. Ao clicar “Salvar”, são gravados campos comerciais selecionados no CRM; confira e cumpra as políticas atuais do Google Places para armazenamento, atribuição e uso. O banco guarda o Place ID e os dados de contato/histórico necessários ao processo interno, mas o tratamento do conteúdo retornado deve ser revisado conforme os termos aplicáveis.
- Os resultados são apresentados com link para a pesquisa do Google Maps, quando fornecido pela API. Revise os requisitos de marca/atribuição aplicáveis antes de colocar em produção.
- Não há envio automático de mensagens. O sistema permite registrar contato e resultado, incluindo “não contatar”.

## Antes de produção
- Adicionar autenticação, autorização, proteção CSRF, rate limit e logs sem segredos.
- Restringir a chave da API por IP e APIs permitidas; definir quotas e alertas de orçamento.
- Publicar política de privacidade/termos se exigidos para a aplicação e revisar a LGPD.
- Não importar nem armazenar avaliações ou dados pessoais sem necessidade e base adequada. Respeitar pedidos de opt-out.
