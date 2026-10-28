<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/src/places.php';
$action = $_GET['action'] ?? '';
try {
    if ($action === 'search') {
        $input = jsonInput();
        $result = placesSearch($config, $input);
        // Deduplicação dentro do lote. Não persistimos automaticamente o conteúdo da API.
        $seen = []; $result['places'] = array_values(array_filter($result['places'], function($p) use (&$seen) {
            if ($p['place_id'] === '' || isset($seen[$p['place_id']])) return false;
            $seen[$p['place_id']] = true; return true;
        }));
        jsonResponse($result);
    }
    if ($action === 'save') {
        $d = jsonInput();
        $placeId = cleanText($d['place_id'] ?? '', 255);
        $name = cleanText($d['name'] ?? '', 255);
        $state = strtoupper(cleanText($d['state'] ?? 'MG', 2));
        $city = cleanText($d['city'] ?? 'Santa Luzia', 120);
        $neighborhood = cleanText($d['neighborhood'] ?? '', 120) ?: null;
        $segment = cleanText($d['segment'] ?? '', 160) ?: null;
        $website = cleanText($d['website'] ?? '', 500) ?: null;
        $phone = cleanText($d['phone'] ?? '', 40) ?: null;
        $status = cleanText($d['website_status'] ?? 'unknown', 30);
        $allowed = ['unknown','not_listed','possible_issue','ok','needs_review'];
        if (!$placeId || !$name || !preg_match('/^[A-Z]{2}$/', $state) || !$city || !in_array($status, $allowed, true)) jsonResponse(['error'=>'Dados inválidos.'], 422);
        $sql = "INSERT INTO prospects (provider,provider_place_id,company_name,state_code,city,neighborhood,segment,website_url,website_status,commercial_phone) VALUES ('google_places',?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE company_name=VALUES(company_name),state_code=VALUES(state_code),city=VALUES(city),neighborhood=VALUES(neighborhood),segment=VALUES(segment),website_url=VALUES(website_url),website_status=VALUES(website_status),commercial_phone=VALUES(commercial_phone)";
        $stmt = $pdo->prepare($sql); $stmt->execute([$placeId,$name,$state,$city,$neighborhood,$segment,$website,$status,$phone]);
        $stmt = $pdo->prepare("SELECT id,contact_status,website_status FROM prospects WHERE provider='google_places' AND provider_place_id=?");
        $stmt->execute([$placeId]);
        jsonResponse(['ok'=>true,'prospect'=>$stmt->fetch()]);
    }
    if ($action === 'list') {
        $page = max(1, (int)($_GET['page'] ?? 1)); $limit = 10; $offset = ($page - 1) * $limit;
        $stmt = $pdo->prepare('SELECT SQL_CALC_FOUND_ROWS id,company_name,state_code,city,neighborhood,segment,website_url,website_status,commercial_phone,contact_status,last_contact_at,updated_at FROM prospects ORDER BY updated_at DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT); $stmt->bindValue(2, $offset, PDO::PARAM_INT); $stmt->execute(); $rows = $stmt->fetchAll();
        $total = (int)$pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        jsonResponse(['items'=>$rows,'page'=>$page,'pages'=>(int)ceil($total/$limit),'total'=>$total]);
    }
    if ($action === 'update') {
        $d = jsonInput(); $id = (int)($d['id'] ?? 0);
        $status = cleanText($d['contact_status'] ?? '', 30);
        $websiteStatus = cleanText($d['website_status'] ?? '', 30);
        $notes = cleanText($d['notes'] ?? '', 5000);
        $contactAllowed = ['not_contacted','prepared','contacted','replied','interested','not_interested','do_not_contact','converted'];
        $websiteAllowed = ['unknown','not_listed','possible_issue','ok','needs_review'];
        if ($id < 1 || !in_array($status,$contactAllowed,true) || !in_array($websiteStatus,$websiteAllowed,true)) jsonResponse(['error'=>'Dados inválidos.'],422);
        $stmt=$pdo->prepare('UPDATE prospects SET contact_status=?,website_status=?,notes=?,last_contact_at=IF(? IN (\'contacted\',\'replied\',\'interested\',\'not_interested\',\'do_not_contact\'),NOW(),last_contact_at) WHERE id=?');
        $stmt->execute([$status,$websiteStatus,$notes,$status,$id]);
        $stmt=$pdo->prepare('SELECT id,company_name,contact_status,website_status FROM prospects WHERE id=?'); $stmt->execute([$id]);
        jsonResponse(['ok'=>true,'prospect'=>$stmt->fetch()]);
    }
    if ($action === 'log-contact') {
        $d=jsonInput(); $id=(int)($d['id']??0); $channel=cleanText($d['channel']??'whatsapp',20); $outcome=cleanText($d['outcome']??'prepared',30); $message=cleanText($d['message']??'',5000); $notes=cleanText($d['notes']??'',2000);
        if ($id<1 || !in_array($channel,['whatsapp','phone','email','other'],true) || !in_array($outcome,['prepared','sent','replied','interested','not_interested','no_response','opt_out'],true)) jsonResponse(['error'=>'Dados inválidos.'],422);
        $pdo->beginTransaction();
        $stmt=$pdo->prepare('INSERT INTO prospect_contacts (prospect_id,channel,outcome,message_text,notes) VALUES (?,?,?,?,?)'); $stmt->execute([$id,$channel,$outcome,$message,$notes]);
        $map=['prepared'=>'prepared','sent'=>'contacted','replied'=>'replied','interested'=>'interested','not_interested'=>'not_interested','no_response'=>'contacted','opt_out'=>'do_not_contact'];
        $stmt=$pdo->prepare('UPDATE prospects SET contact_status=?,last_contact_at=IF(? IN (\'sent\',\'replied\',\'interested\',\'not_interested\',\'no_response\',\'opt_out\'),NOW(),last_contact_at) WHERE id=?'); $stmt->execute([$map[$outcome],$outcome,$id]);
        $pdo->commit(); jsonResponse(['ok'=>true]);
    }
    jsonResponse(['error'=>'Ação não encontrada.'],404);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonResponse(['error'=>$e instanceof InvalidArgumentException ? $e->getMessage() : $e->getMessage()], 500);
}
