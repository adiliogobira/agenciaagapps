<?php
$states = ['AC'=>'Acre','AL'=>'Alagoas','AP'=>'Amapá','AM'=>'Amazonas','BA'=>'Bahia','CE'=>'Ceará','DF'=>'Distrito Federal','ES'=>'Espírito Santo','GO'=>'Goiás','MA'=>'Maranhão','MT'=>'Mato Grosso','MS'=>'Mato Grosso do Sul','MG'=>'Minas Gerais','PA'=>'Pará','PB'=>'Paraíba','PR'=>'Paraná','PE'=>'Pernambuco','PI'=>'Piauí','RJ'=>'Rio de Janeiro','RN'=>'Rio Grande do Norte','RS'=>'Rio Grande do Sul','RO'=>'Rondônia','RR'=>'Roraima','SC'=>'Santa Catarina','SP'=>'São Paulo','SE'=>'Sergipe','TO'=>'Tocantins'];
$segments = ['Todos os segmentos','Restaurantes e lanchonetes','Depósitos de materiais de construção','Oficinas mecânicas','Salões de beleza e barbearias','Lojas de roupas','Pet shops e clínicas veterinárias','Mercados e mercearias','Clínicas e consultórios','Academias','Imobiliárias','Outros'];
?>
<!doctype html>
<html lang="pt-BR">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>AG Apps | Prospecção Comercial</title>
		<meta name="description" content="Painel interno de prospecção comercial da AG Apps.">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<style>body{background:#f4f6f9}.brand{font-weight:800;letter-spacing:.2px}.card{border:0;box-shadow:0 3px 16px #1522380d}.small-muted{font-size:.86rem;color:#6c757d}.company-name{font-weight:700}.table td{vertical-align:middle}.status{min-width:155px}.nowrap{white-space:nowrap}.spinner-border{width:1rem;height:1rem}</style>
	</head>
	<body>
		<nav class="navbar navbar-dark bg-primary">
			<div class="container">
				<span class="navbar-brand brand">AG Apps <span class="fw-normal">/ Prospecção</span>
			</span>
			<span class="text-white-50 small">Painel comercial</span>
		</div>
	</nav>
	<main class="container py-4">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
			<div>
				<h1 class="h3 mb-1">Encontrar oportunidades</h1>
				<p class="text-secondary mb-0">Pesquise empresas reais e acompanhe o histórico comercial.</p>
			</div>
			<button id="showSaved" class="btn btn-outline-primary">Ver empresas salvas</button>
		</div>
		<div class="alert alert-info small">
		<strong>Fonte de dados:</strong> Google Places API (New). Configure sua chave e habilite faturamento no Google Cloud. Resultados da pesquisa são temporários nesta tela; salve apenas os dados necessários ao histórico comercial e cumpra as políticas de atribuição e armazenamento do provedor.</div>
		<section class="card mb-4">
			<div class="card-body p-4">
				<form id="searchForm" class="row g-3">
					<div class="col-md-3">
						<label for="state" class="form-label">Estado</label>
						<select id="state" class="form-select">
							<?php foreach($states as $uf=>$name): ?>
							<option value="<?=htmlspecialchars($uf)?>" <?=$uf==='MG'?'selected':''?>>
								<?=htmlspecialchars($name.' ('.$uf.')')?>
							</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3">
						<label for="city" class="form-label">Cidade</label>
						<input id="city" class="form-control" value="Santa Luzia" required>
					</div>
					<div class="col-md-3">
						<label for="neighborhood" class="form-label">Bairro (opcional)</label>
						<input id="neighborhood" class="form-control" placeholder="Ex.: São Benedito">
					</div>
					<div class="col-md-3">
						<label for="segment" class="form-label">Segmento</label>
						<select id="segment" class="form-select">
							<?php foreach($segments as $s): ?>
							<option>
								<?=htmlspecialchars($s)?>
							</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-6">
						<label for="priority" class="form-label">Priorizar</label>
						<select id="priority" class="form-select">
							<option value="possible_issue" selected>Site com possível problema / não informado</option>
							<option value="not_listed">Sem site informado pela fonte</option>
							<option value="all">Todas as empresas encontradas</option>
						</select>
						<div class="form-text">A ausência de site no resultado não prova que a empresa não tenha site.</div>
					</div>
					<div class="col-md-6 d-flex align-items-end">
						<button id="searchBtn" class="btn btn-primary w-100" type="submit">
						<span class="buttonText">Pesquisar empresas</span>
						</button>
					</div>
				</form>
			</div>
		</section>
		<section class="card">
			<div class="card-body p-0">
				<div class="p-3 border-bottom d-flex justify-content-between flex-wrap gap-2">
					<div>
						<h2 class="h5 mb-1" id="resultsTitle">Resultados da pesquisa</h2>
						<div class="small-muted" id="resultCount">Informe os filtros e pesquise.</div>
					</div>
					<a id="sourceLink" class="small d-none" target="_blank" rel="noopener">Abrir pesquisa no Google Maps</a>
				</div>
				<div id="message" class="px-3 pt-3">
				</div>
				<div class="table-responsive">
					<table class="table table-hover mb-0">
						<thead class="table-light">
							<tr>
								<th>Empresa</th>
								<th>Endereço</th>
								<th>Site / avaliação</th>
								<th>Telefone</th>
								<th>Oportunidade</th>
								<th>Ação</th>
							</tr>
						</thead>
						<tbody id="resultsBody">
							<tr>
								<td colspan="6" class="text-center text-secondary py-5">Nenhuma pesquisa realizada.</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
					<span id="pageInfo" class="small-muted">Até 10 resultados por lote.</span>
					<button id="nextPage" class="btn btn-outline-primary btn-sm" disabled>Carregar mais 10</button>
				</div>
			</div>
		</section>
		<section class="card mt-4 d-none" id="savedSection">
			<div class="card-body p-0">
				<div class="p-3 border-bottom">
					<h2 class="h5 mb-0">Empresas salvas</h2>
				</div>
				<div class="table-responsive">
					<table class="table table-hover mb-0">
						<thead class="table-light">
							<tr>
								<th>Empresa</th>
								<th>Site</th>
								<th>Status do contato</th>
								<th>Status do site</th>
								<th>Observações / atualizar</th>
							</tr>
						</thead>
						<tbody id="savedBody">
						</tbody>
					</table>
				</div>
				<div class="p-3 border-top d-flex justify-content-between align-items-center">
					<span id="savedInfo" class="small-muted">
					</span>
					<div class="btn-group">
						<button class="btn btn-outline-secondary btn-sm" id="savedPrev">Anterior</button>
						<button class="btn btn-outline-secondary btn-sm" id="savedNext">Próxima</button>
					</div>
				</div>
			</div>
		</section>
	<footer class="text-center text-secondary small py-4">AG Apps Soluções · Ferramenta interna de prospecção · Contatos devem ser feitos individualmente e respeitando pedidos de não contato.</footer>
</main>
<script src="https://code.jquery.com/jquery-3.7.1.min.js">
</script>
<script>
$(function() {
	let nextToken = null,
		seen = new Set(),
		savedPage = 1,
		savedPages = 1,
		lastQuery = null;
	const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#39;'
	} [c]));
	const statusLabels = {
		not_contacted: 'Não contatada',
		prepared: 'Mensagem preparada',
		contacted: 'Contatada',
		replied: 'Respondeu',
		interested: 'Interessada',
		not_interested: 'Sem interesse',
		do_not_contact: 'Não contatar',
		converted: 'Convertida'
	};
	const siteLabels = {
		unknown: 'Não avaliado',
		not_listed: 'Site não informado',
		possible_issue: 'Possível problema',
		ok: 'Site verificado',
		needs_review: 'Revisar manualmente'
	};

	function alertMsg(msg, type = 'danger') {
		$('#message').html(`<div class="alert alert-${type} py-2">${esc(msg)}</div>`)
	}

	function setBusy(b) {
		$('#searchBtn').prop('disabled', b).find('.buttonText').text(b ? 'Pesquisando...' : 'Pesquisar empresas')
	}

	function formData(token = null) {
		return {
			state: $('#state').val(),
			city: $('#city').val().trim(),
			neighborhood: $('#neighborhood').val().trim(),
			segment: $('#segment').val(),
			pageToken: token
		}
	}

	function renderPlace(p) {
		const noSite = !p.website;
		const priority = $('#priority').val();
		if (priority === 'not_listed' && !noSite) return '';
		if (seen.has(p.place_id)) return '';
		seen.add(p.place_id);
		const rate = p.rating == null ? 'Sem nota' : `${esc(p.rating)} ★ (${esc(p.reviews??0)} avaliações)`;
		return `<tr data-place="${esc(p.place_id)}"><td><div class="company-name">${esc(p.name)}</div><div class="small-muted">ID do local: ${esc(p.place_id)}</div></td><td>${esc(p.address||'Endereço não informado')}</td><td>${p.website?`<a href="${esc(p.website)}" target="_blank" rel="noopener">Abrir site</a>`:'<span class="badge text-bg-warning">Site não informado</span>'}<div class="small-muted mt-1">${rate}</div></td><td>${esc(p.phone||'Não informado')}</td><td><select class="form-select form-select-sm website-status"><option value="${noSite?'not_listed':'needs_review'}">${noSite?'Site não informado':'Revisar site manualmente'}</option><option value="possible_issue">Possível problema confirmado</option><option value="needs_review">Revisar manualmente</option><option value="ok">Site verificado</option></select></td><td><button class="btn btn-sm btn-primary savePlace">Salvar</button></td></tr>`
	}
	async function search(token = null) {
		setBusy(true);
		$('#message').empty();
		try {
			const q = formData(token);
			lastQuery = q;
			const r = await fetch('api.php?action=search', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(q)
			});
			const data = await r.json();
			if (!r.ok || data.error) throw new Error(data.error || 'Erro na pesquisa.');
			if (!token) {
				$('#resultsBody').empty();
				seen.clear()
			}
			let shown = 0;
			(data.places || []).forEach(p => {
				const row = renderPlace(p);
				if (row) {
					$('#resultsBody').append(row);
					shown++
				}
			});
			if (!$('#resultsBody').children().length) $('#resultsBody').html('<tr><td colspan="6" class="text-center text-secondary py-4">Nenhuma empresa corresponde ao filtro. Tente outra prioridade ou segmento.</td></tr>');
			nextToken = data.nextPageToken || null;
			$('#nextPage').prop('disabled', !nextToken);
			$('#resultCount').text(`${seen.size} empresas únicas neste conjunto de resultados (máximo de 10 por lote solicitado).`);
			$('#pageInfo').text(nextToken ? 'Há mais resultados disponíveis.' : 'Não há outra página informada pela fonte.');
			$('#sourceLink').toggleClass('d-none', !data.searchUri).attr('href', data.searchUri || '#');
		} catch (e) {
			alertMsg(e.message);
			if (!token) $('#resultsBody').html('<tr><td colspan="6" class="text-center text-danger py-4">Não foi possível carregar os resultados.</td></tr>')
		} finally {
			setBusy(false)
		}
	}
	$('#searchForm').on('submit', e => {
		e.preventDefault();
		if (!$('#city').val().trim()) {
			alertMsg('Informe a cidade.');
			return
		}
		search(null)
	});
	$('#nextPage').on('click', () => {
		if (nextToken) search(nextToken)
	});
	$('#resultsBody').on('click', '.savePlace', async function() {
		const tr = $(this).closest('tr'),
			p = (window.currentPlaces || []).find(x => x.place_id === tr.data('place'));
		if (!p) {
			alertMsg('Os dados desse resultado não estão disponíveis; faça a pesquisa novamente.');
			return
		}
		const status = tr.find('.website-status').val();
		const payload = {
			...p,
			...formData(),
			website_status: status
		};
		try {
			const r = await fetch('api.php?action=save', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(payload)
			});
			const d = await r.json();
			if (!r.ok || d.error) throw new Error(d.error || 'Erro ao salvar');
			$(this).removeClass('btn-primary').addClass('btn-success').text('Salvo').prop('disabled', true);
			alertMsg('Empresa salva no histórico comercial.', 'success')
		} catch (e) {
			alertMsg(e.message)
		}
	});
	// Retém apenas os resultados em memória enquanto a página estiver aberta; não é persistência de API.
	const originalSearch = search;
	search = async function(token = null) {
		await originalSearch(token)
	};
	// Guarda a resposta atual para permitir salvar uma empresa selecionada.
	const _fetch = window.fetch;
	window.fetch = async function(input, init) {
		const res = await _fetch(input, init);
		if (String(input).includes('action=search')) {
			try {
				const clone = res.clone();
				const data = await clone.json();
				window.currentPlaces = [...(window.currentPlaces || []), ...(tokenless(data))]
			} catch (e) {}
		}
		return res
	};

	function tokenless(data) {
		return data.places || []
	}
	$('#showSaved').on('click', () => {
		$('#savedSection').removeClass('d-none');
		loadSaved(1);
		$('html,body').animate({
			scrollTop: $('#savedSection').offset().top - 15
		}, 250)
	});
	async function loadSaved(page) {
		savedPage = page;
		try {
			const r = await fetch(`api.php?action=list&page=${page}`),
				d = await r.json();
			if (!r.ok || d.error) throw new Error(d.error || 'Erro ao carregar');
			savedPages = d.pages || 1;
			$('#savedBody').html((d.items || []).map(p => `<tr data-id="${p.id}"><td><strong>${esc(p.company_name)}</strong><div class="small-muted">${esc(p.city)} / ${esc(p.state_code)}</div></td><td>${p.website_url?`<a href="${esc(p.website_url)}" target="_blank" rel="noopener">Abrir</a>`:'—'}</td><td><select class="form-select form-select-sm contact-status">${Object.entries(statusLabels).map(([v,l])=>`<option value="${v}" ${p.contact_status===v?'selected':''}>${l}</option>`).join('')}</select></td><td><select class="form-select form-select-sm saved-website-status">${Object.entries(siteLabels).map(([v,l])=>`<option value="${v}" ${p.website_status===v?'selected':''}>${l}</option>`).join('')}</select></td><td><input class="form-control form-control-sm notes" placeholder="Anotação interna" value="${esc(p.notes||'')}"><button class="btn btn-sm btn-outline-primary mt-1 updateProspect">Atualizar</button> <button class="btn btn-sm btn-outline-secondary mt-1 logContact">Registrar contato</button></td></tr>`).join('') || '<tr><td colspan="5" class="text-center py-4">Nenhuma empresa salva.</td></tr>');
			$('#savedInfo').text(`${d.total||0} registros · Página ${page} de ${savedPages}`);
			$('#savedPrev').prop('disabled', page <= 1);
			$('#savedNext').prop('disabled', page >= savedPages)
		} catch (e) {
			alertMsg(e.message)
		}
	}
	$('#savedPrev').on('click', () => {
		if (savedPage > 1) loadSaved(savedPage - 1)
	});
	$('#savedNext').on('click', () => {
		if (savedPage < savedPages) loadSaved(savedPage + 1)
	});
	$('#savedBody').on('click', '.updateProspect', async function() {
		const tr = $(this).closest('tr');
		const payload = {
			id: Number(tr.data('id')),
			contact_status: tr.find('.contact-status').val(),
			website_status: tr.find('.saved-website-status').val(),
			notes: tr.find('.notes').val()
		};
		try {
			const r = await fetch('api.php?action=update', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(payload)
				}),
				d = await r.json();
			if (!r.ok || d.error) throw new Error(d.error || 'Erro ao atualizar');
			alertMsg('Histórico atualizado.', 'success')
		} catch (e) {
			alertMsg(e.message)
		}
	});
	$('#savedBody').on('click', '.logContact', async function() {
		const tr = $(this).closest('tr');
		const outcome = prompt('Resultado: prepared, sent, replied, interested, not_interested, no_response ou opt_out', 'prepared');
		if (!outcome) return;
		const message = prompt('Texto da mensagem (opcional):', '') || '';
		try {
			const r = await fetch('api.php?action=log-contact', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({
						id: Number(tr.data('id')),
						channel: 'whatsapp',
						outcome,
						message
					})
				}),
				d = await r.json();
			if (!r.ok || d.error) throw new Error(d.error || 'Erro ao registrar');
			alertMsg('Contato registrado.', 'success');
			loadSaved(savedPage)
		} catch (e) {
			alertMsg(e.message)
		}
	});
});
</script>
</body>
</html>