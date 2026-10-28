<?php
declare (strict_types = 1);
function placesSearch(array $config, array $input): array
{
    $key = trim((string) ($config['google_places_api_key'] ?? ''));
    if ($key === '' || $key === 'COLOQUE_SUA_CHAVE_AQUI') {
        throw new RuntimeException('Configure google_places_api_key em config/config.php.');
    }
    $state = strtoupper(trim((string) ($input['state'] ?? 'MG')));
    $city = trim((string) ($input['city'] ?? 'Santa Luzia'));
    $neighborhood = trim((string) ($input['neighborhood'] ?? ''));
    $segment = trim((string) ($input['segment'] ?? 'Todos os segmentos'));
    if (!preg_match('/^[A-Z]{2}$/', $state) || $city === '') {
        throw new InvalidArgumentException('Informe estado e cidade válidos.');
    }

    $parts = [];
    if ($segment !== '' && $segment !== 'Todos os segmentos') {
        $parts[] = $segment;
    }

    $parts[] = 'em ' . ($neighborhood !== '' ? $neighborhood . ', ' : '') . $city . ', ' . $state . ', Brasil';
    $body = ['textQuery' => implode(' ', $parts), 'pageSize' => 10, 'languageCode' => 'pt-BR', 'regionCode' => 'BR'];
    if (!empty($input['pageToken'])) {
        $body['pageToken'] = (string) $input['pageToken'];
    }

    $ch = curl_init('https://places.googleapis.com/v1/places:searchText');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . $key,
            'X-Goog-FieldMask: places.id,places.displayName,places.formattedAddress,places.websiteUri,places.nationalPhoneNumber,places.rating,places.userRatingCount,nextPageToken,searchUri',
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
    ]);
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        throw new RuntimeException('Falha de conexão com a API: ' . $error);
    }

    $result = json_decode($raw, true);
    if ($code < 200 || $code >= 300) {
        $msg = $result['error']['message'] ?? ('HTTP ' . $code);
        throw new RuntimeException('Google Places: ' . $msg);
    }
    $places = [];
    foreach (($result['places'] ?? []) as $p) {
        $places[] = [
            'place_id' => (string) ($p['id'] ?? ''),
            'name' => (string) ($p['displayName']['text'] ?? 'Nome não informado'),
            'address' => (string) ($p['formattedAddress'] ?? ''),
            'website' => (string) ($p['websiteUri'] ?? ''),
            'phone' => (string) ($p['nationalPhoneNumber'] ?? ''),
            'rating' => $p['rating'] ?? null,
            'reviews' => $p['userRatingCount'] ?? null,
        ];
    }
    return ['places' => $places, 'nextPageToken' => $result['nextPageToken'] ?? null, 'searchUri' => $result['searchUri'] ?? null];
}
