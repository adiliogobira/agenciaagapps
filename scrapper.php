<?php
declare(strict_types=1);
session_start();

/**
 * AG Apps - Gerador de prospecção
 * Uso: PHP 8.1+ com servidor web. Não raspa o Google Maps automaticamente:
 * analisa texto colado pelo operador para evitar scraping não autorizado.
 */
function esc(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function extractPhone(string $text): string {
    if (preg_match('/(?:\+?55\s*)?(?:\(?\d{2}\)?\s*)?(?:9?\d{4})[\s.-]?\d{4}/', $text, $m)) {
        return trim($m[0]);
    }
    return '';
}
function extractRating(string $text): string {
    if (preg_match('/(?:^|\n)\s*([0-5],[0-9])\s*(?:\(|⭐|★)?/u', $text, $m)) return $m[1];
    if (preg_match('/([0-5],[0-9])\s*(?:estrelas|stars|de 5)/iu', $text, $m)) return $m[1];
    return '';
}
function extractReviewCount(string $text): string {
    if (preg_match('/([\d.]+)\s*(?:avaliações|avaliação|reviews|comentários)/iu', $text, $m)) return $m[1];
    return '';
}
function detectBusinessName(string $text): string {
    $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', $text) ?: [])));
    foreach ($lines as $line) {
        if (mb_strlen($line) > 2 && mb_strlen($line) < 100 &&
            !preg_match('/(avaliações|avaliação|como chegar|aberto|fechado|horário|compartilhar|ligar|site|www\.|http)/iu', $line)) {
            return $line;
        }
    }
    return '';
}
function buildMessage(array $d): string {
    $name = trim($d['name'] ?? '') ?: 'sua empresa';
    $segment = trim($d['segment'] ?? '') ?: 'negócio local';
    $location = trim($d['location'] ?? '');
    $rating = trim($d['rating'] ?? '');
    $reviews = trim($d['reviews'] ?? '');
    $praise = trim($d['praise'] ?? '');
    $opportunity = trim($d['opportunity'] ?? '');
    $context = $location ? " Vi que vocês atendem na região de {$location}." : '';
    $reputation = ($rating || $reviews) ? "\n\nTambém encontrei o perfil de vocês no Google" .
        ($rating ? " com nota {$rating}/5" : '') .
        ($reviews ? " e {$reviews} avaliações" : '') . "." : '';
    $positive = $praise ? "\nPelo que observei, os clientes destacam {$praise}." : '';
    $gap = $opportunity ? "\nIdentifiquei uma oportunidade de melhorar a apresentação digital: {$opportunity}." :
        "\nAcredito que uma apresentação digital mais organizada pode facilitar o contato de novos clientes.";
    return "Olá, tudo bem?\n\nMeu nome é [SEU NOME], sou da AG Apps Soluções. Trabalho com criação de sites, catálogos online e presença digital para empresas.\n\nEncontrei a {$name}, do segmento de {$segment}, durante uma pesquisa de negócios locais." .
        $context . $reputation . $positive . $gap .
        "\n\nPosso preparar uma prévia visual, sem compromisso, mostrando como um site profissional poderia apresentar melhor os serviços, localização, diferenciais e formas de contato de vocês.\n\nSe fizer sentido, posso te mostrar a ideia?\n\nwww.agapps.com.br";
}
function buildPrompt(array $d): string {
    $name = trim($d['name'] ?? '') ?: '[NOME DA EMPRESA]';
    return "Crie um site institucional responsivo em HTML5, CSS e JavaScript para a empresa “{$name}”, usando as informações abaixo como referência. Gere um único arquivo index.html completo, com CSS no próprio arquivo, visual moderno e profissional, excelente experiência mobile, acessibilidade, SEO básico (title, meta description, headings semânticos), botão de WhatsApp quando houver telefone confirmado e chamadas para ação claras.\n\nIMPORTANTE: não invente serviços, preços, endereço, horário, certificações, estatísticas ou depoimentos. Se algum dado estiver ausente, omita-o ou use um marcador editável. Não copie avaliações de clientes como depoimentos publicados sem autorização; comentários abaixo servem apenas para entender os pontos fortes do negócio. Use imagens genéricas de demonstração ou espaços reservados e indique onde substituí-las. O objetivo é uma PRÉVIA CONCEITUAL, não afirmar que o site já pertence ou foi aprovado pela empresa.\n\nDADOS COLETADOS:\nEmpresa: ".($d['name'] ?? '')."\nSegmento: ".($d['segment'] ?? '')."\nLocalização: ".($d['location'] ?? '')."\nTelefone: ".($d['phone'] ?? '')."\nSite atual: ".($d['site_status'] ?? '')."\nInstagram: ".($d['instagram_status'] ?? '')."\nPerfil Google: ".($d['google_status'] ?? '')."\nNota Google: ".($d['rating'] ?? '')."\nQuantidade de avaliações: ".($d['reviews'] ?? '')."\nPontos positivos percebidos: ".($d['praise'] ?? '')."\nOportunidades identificadas: ".($d['opportunity'] ?? '')."\nTexto original colado: ".($d['raw'] ?? '')."\n\nEntregue somente o HTML completo, pronto para salvar como index.html. Não alegue que o site melhora posições automaticamente no Google; foque em apresentação, clareza e facilidade de contato.";
}
$defaults = [
    'name'=>'','segment'=>'Comércio local','location'=>'','phone'=>'','raw'=>'',
    'rating'=>'','reviews'=>'','praise'=>'','opportunity'=>'',
    'site_status'=>'Não possui site','instagram_status'=>'Existe, mas pode melhorar','google_status'=>'Perfil incompleto'
];
$data = $defaults;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($defaults as $key => $default) {
        if (isset($_POST[$key]) && is_string($_POST[$key])) $data[$key] = trim($_POST[$key]);
    }
    $action = $_POST['action'] ?? 'generate';
    if ($action === 'parse') {
        $raw = $data['raw'];
        if (!$data['name']) $data['name'] = detectBusinessName($raw);
        if (!$data['phone']) $data['phone'] = extractPhone($raw);
        if (!$data['rating']) $data['rating'] = extractRating($raw);
        if (!$data['reviews']) $data['reviews'] = extractReviewCount($raw);
        if (!$data['location'] && preg_match('/(?:Endereço|Address|Localização)\s*:?\s*(.+)/iu', $raw, $m)) $data['location'] = trim($m[1]);
        $message = '';
        $prompt = '';
    } else {
        $message = buildMessage($data);
        $prompt = buildPrompt($data);
    }
} else {
    $message = '';
    $prompt = '';
}
$phoneDigits = preg_replace('/\D+/', '', $data['phone']);
if ($phoneDigits && str_starts_with($phoneDigits, '0')) $phoneDigits = substr($phoneDigits, 1);
if ($phoneDigits && !str_starts_with($phoneDigits, '55') && strlen($phoneDigits) >= 10) $phoneDigits = '55'.$phoneDigits;
$waUrl = $phoneDigits ? 'https://wa.me/'.$phoneDigits.'?text='.rawurlencode($message) : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>AG Apps | Prospecção Inteligente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--ink:#17212f;--muted:#6b7280;--brand:#6558e8;--brand2:#4237b8;--line:#e5e7eb;--soft:#f6f7fb}
body{background:var(--soft);color:var(--ink);font-family:system-ui,-apple-system,Segoe UI,sans-serif}
.navbar{background:#17172c}.brand-mark{background:#6558e8;color:#fff;border-radius:10px;padding:8px 10px;font-weight:800}
.hero{background:linear-gradient(120deg,#24234b,#4b42a5);color:white;border-radius:22px;padding:28px}
.card{border:1px solid var(--line);border-radius:18px;box-shadow:0 8px 28px #17212f0a}
.form-label{font-weight:650;font-size:.9rem}.form-control,.form-select{border-color:#d9dce5;padding:.7rem .8rem}
.form-control:focus,.form-select:focus{border-color:var(--brand);box-shadow:0 0 0 .2rem #6558e822}
.btn-primary{background:var(--brand);border-color:var(--brand)}.btn-primary:hover{background:var(--brand2)}
textarea{min-height:120px}.output{min-height:250px;font-size:.92rem;line-height:1.55}
.section-kicker{font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;color:var(--brand);font-weight:800}
</style>
</head>
<body>
<nav class="navbar navbar-dark py-3"><div class="container"><span class="navbar-brand d-flex align-items-center gap-2"><span class="brand-mark">AG</span><span>AG Apps <small class="d-block text-white-50" style="font-size:.68rem">PROSPECÇÃO INTELIGENTE</small></span></span><a class="btn btn-outline-light btn-sm" href="https://www.agapps.com.br" target="_blank" rel="noopener">agapps.com.br ↗</a></div></nav>
<main class="container py-4 py-lg-5">
<section class="hero mb-4 mb-lg-5"><div class="row align-items-center g-3"><div class="col-lg-8"><div class="text-uppercase small fw-bold opacity-75 mb-2">Pesquisa • Personalização • Conversão</div><h1 class="h2 fw-bold">Transforme uma pesquisa em uma oportunidade comercial.</h1><p class="mb-0 opacity-75">Cole as informações públicas que você coletou, revise os campos e gere a mensagem, o link do WhatsApp e o prompt para criar uma prévia de site.</p></div><div class="col-lg-4 text-lg-end"><span class="badge text-bg-light text-dark p-2">PHP + Bootstrap + jQuery</span></div></div></section>
<form method="post" id="prospectForm">
<div class="row g-4">
<div class="col-lg-6">
<div class="card p-3 p-md-4 mb-4"><div class="section-kicker mb-1">01 / Entrada</div><h2 class="h5 fw-bold">Dados coletados no Google</h2><p class="small text-secondary">Cole aqui o texto copiado do perfil. A extração é uma sugestão: confira os dados antes de entrar em contato.</p>
<label class="form-label" for="raw">Texto do Google Maps / pesquisa</label><textarea class="form-control mb-3" id="raw" name="raw" placeholder="Cole aqui nome, endereço, telefone, nota, avaliações, serviços e outras informações..."><?=esc($data['raw'])?></textarea>
<button class="btn btn-outline-primary w-100" type="submit" name="action" value="parse">✦ Analisar texto e preencher campos</button>
</div>
<div class="card p-3 p-md-4"><div class="section-kicker mb-1">02 / Revisão</div><h2 class="h5 fw-bold">Confirme os dados da empresa</h2>
<div class="mb-3"><label class="form-label">Nome da empresa</label><input class="form-control" name="name" value="<?=esc($data['name'])?>" placeholder="Nome comercial"></div>
<div class="mb-3"><label class="form-label">Segmento</label><input class="form-control" name="segment" value="<?=esc($data['segment'])?>" placeholder="Ex.: oficina mecânica, salão..."></div>
<div class="mb-3"><label class="form-label">Endereço / região</label><input class="form-control" name="location" value="<?=esc($data['location'])?>" placeholder="Bairro e cidade"></div>
<div class="mb-3"><label class="form-label">Telefone / WhatsApp</label><input class="form-control" id="phone" name="phone" value="<?=esc($data['phone'])?>" placeholder="(31) 99999-9999"><div class="form-text">Pode editar ou informar o número manualmente.</div></div>
<div class="row g-3"><div class="col-6"><label class="form-label">Nota Google</label><input class="form-control" name="rating" value="<?=esc($data['rating'])?>" placeholder="4,7"></div><div class="col-6"><label class="form-label">Nº avaliações</label><input class="form-control" name="reviews" value="<?=esc($data['reviews'])?>" placeholder="86"></div></div>
<div class="mt-3 mb-3"><label class="form-label">O que os clientes elogiam?</label><input class="form-control" name="praise" value="<?=esc($data['praise'])?>" placeholder="Ex.: atendimento, qualidade..."></div>
<div class="mb-3"><label class="form-label">Oportunidade identificada</label><textarea class="form-control" name="opportunity" rows="2" placeholder="Ex.: não tem site; serviços sem descrição"><?=esc($data['opportunity'])?></textarea></div>
<div class="row g-3"><div class="col-md-4"><label class="form-label">Site</label><select class="form-select" name="site_status"><?php foreach(['Não possui site','Site desatualizado','Site bem organizado','Não verificado'] as $v):?><option <?= $data['site_status']===$v?'selected':''?>><?=esc($v)?></option><?php endforeach;?></select></div><div class="col-md-4"><label class="form-label">Instagram</label><select class="form-select" name="instagram_status"><?php foreach(['Não possui Instagram','Existe, mas pode melhorar','Bem organizado','Não verificado'] as $v):?><option <?= $data['instagram_status']===$v?'selected':''?>><?=esc($v)?></option><?php endforeach;?></select></div><div class="col-md-4"><label class="form-label">Perfil Google</label><select class="form-select" name="google_status"><?php foreach(['Perfil incompleto','Poucas fotos ou informações','Bem configurado','Não encontrado','Não verificado'] as $v):?><option <?= $data['google_status']===$v?'selected':''?>><?=esc($v)?></option><?php endforeach;?></select></div></div>
<button class="btn btn-primary w-100 mt-4" type="submit" name="action" value="generate">Gerar mensagem e prompt ↗</button>
</div>
</div>
<div class="col-lg-6">
<div class="card p-3 p-md-4 mb-4"><div class="section-kicker mb-1">03 / Abordagem</div><h2 class="h5 fw-bold">Mensagem personalizada</h2><p class="small text-secondary">Revise o texto antes de enviar. A mensagem não é enviada automaticamente.</p>
<textarea class="form-control output" id="message" readonly><?=esc($message)?></textarea>
<div class="d-grid d-sm-flex gap-2 mt-3"><button class="btn btn-outline-secondary flex-fill" type="button" id="copyMessage">Copiar mensagem</button><?php if($waUrl):?><a class="btn btn-success flex-fill" id="waLink" href="<?=esc($waUrl)?>" target="_blank" rel="noopener">Abrir WhatsApp ↗</a><?php else:?><button class="btn btn-success flex-fill" type="button" disabled>Informe o telefone para abrir WhatsApp</button><?php endif;?></div>
<div class="form-text mt-2">Abre o WhatsApp com o texto preenchido. Você confere e clica em enviar no próprio WhatsApp.</div>
</div>
<div class="card p-3 p-md-4"><div class="section-kicker mb-1">04 / Criação da prévia</div><h2 class="h5 fw-bold">Prompt para gerar o site em uma IA</h2><p class="small text-secondary">Copie o prompt e cole na IA que você usa para criar o HTML demonstrativo.</p>
<textarea class="form-control output" id="prompt" readonly><?=esc($prompt)?></textarea>
<button class="btn btn-outline-primary w-100 mt-3" type="button" id="copyPrompt">Copiar prompt para IA</button>
<div class="alert alert-light border mt-3 mb-0 small"><strong>Boas práticas:</strong> use os dados como referência, não publique avaliações sem autorização e não invente serviços ou resultados.</div>
</div>
</div></div>
</form>
<footer class="text-center text-secondary small py-4">AG Apps Soluções · Ferramenta interna de prospecção · <a href="https://www.agapps.com.br">www.agapps.com.br</a></footer>
</main>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script><script>
$(function(){
 function copyFrom(id, button, label){
   const el=document.getElementById(id), text=el.value;
   if(!text.trim()){alert('Gere o conteúdo primeiro.');return;}
   if(navigator.clipboard && window.isSecureContext){navigator.clipboard.writeText(text).then(()=>{const old=button.text();button.text('Copiado!');setTimeout(()=>button.text(old),1500);});}
   else {el.focus();el.select();document.execCommand('copy');button.text('Copiado!');setTimeout(()=>button.text(label),1500);}
 }
 $('#copyMessage').on('click',function(){copyFrom('message',$(this),'Copiar mensagem')});
 $('#copyPrompt').on('click',function(){copyFrom('prompt',$(this),'Copiar prompt para IA')});
 $('#prospectForm').on('submit',function(){ $('button[type=submit]').prop('disabled',true); });
});
</script>
</body></html>

'''

(base/"index.php").write_text(index_php, encoding="utf-8")
readme = """# AG Apps — Prospecção Inteligente

## Requisitos
- PHP 8.1+ (extensão mbstring recomendada)
- Apache/Nginx com PHP habilitado
- Conexão com a internet para carregar Bootstrap 5 e jQuery por CDN

## Instalação
1. Copie `index.php` para uma pasta publicada pelo seu servidor web.
2. Acesse a página pelo navegador (não abra o arquivo diretamente como HTML).
3. Cole o texto que você coletou manualmente no Google Maps/pesquisa.
4. Clique em **Analisar texto e preencher campos**.
5. Confira e corrija os campos. Clique em **Gerar mensagem e prompt**.
6. Copie a mensagem ou abra o WhatsApp. O envio é manual, dentro do WhatsApp.
7. Copie o prompt para sua IA gerar uma prévia conceitual em HTML.

## Limites e segurança
- A ferramenta não acessa nem raspa o Google Maps automaticamente. Ela analisa texto colado por você.
- A extração automática é heurística e deve ser revisada; pode errar nome, telefone ou nota.
- O link `wa.me` apenas abre uma conversa com texto pré-preenchido; não envia mensagens sozinho.
- Evite disparos em massa. Faça contato individual, relevante e respeite pedidos para não receber novas mensagens.
- Não publique avaliações ou dados pessoais sem verificar permissões e necessidade.
- Para produção, considere proteção CSRF, limite de tamanho do texto, autenticação e registro mínimo de consentimento/contato.
