/* =====================================================================
   Diaconia — Tema e helpers de gráficos (Chart.js 4)
   Uso:
     AppCharts.bar('canvasId', labels, [{label, data, color}], {money:true})
     AppCharts.pie('canvasId', labels, data)
     AppCharts.doughnut('canvasId', labels, data, {center:'120'})
     AppCharts.line('canvasId', labels, series)
   ===================================================================== */
(function (global) {
  'use strict';

  if (typeof Chart === 'undefined') {
    console.warn('Chart.js não carregado.');
    return;
  }

  /* ---------- Paleta da marca ---------- */
  var PALETTE = [
    '#6366f1', // índigo (marca)
    '#8b5cf6', // violeta
    '#10b981', // verde
    '#f59e0b', // âmbar
    '#0ea5e9', // azul
    '#ef4444', // vermelho
    '#ec4899', // rosa
    '#14b8a6', // teal
    '#a3a3a3'  // cinza
  ];

  var SEMANTIC = {
    entrada: '#10b981',
    saida: '#ef4444',
    ativo: '#10b981',
    inativo: '#94a3b8',
    disciplina: '#f59e0b',
    transferido: '#6366f1',
    preenchida: '#10b981',
    aberta: '#f59e0b'
  };

  /* ---------- Defaults globais ---------- */
  Chart.defaults.font.family = "'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif";
  Chart.defaults.font.size = 12;
  Chart.defaults.color = '#64748b';
  Chart.defaults.plugins.legend.position = 'bottom';
  Chart.defaults.plugins.legend.labels.usePointStyle = true;
  Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
  Chart.defaults.plugins.legend.labels.boxWidth = 8;
  Chart.defaults.plugins.legend.labels.padding = 16;
  Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15,23,42,.92)';
  Chart.defaults.plugins.tooltip.padding = 12;
  Chart.defaults.plugins.tooltip.cornerRadius = 10;
  Chart.defaults.plugins.tooltip.titleFont = { weight: '700', size: 12 };
  Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };
  Chart.defaults.plugins.tooltip.displayColors = true;
  Chart.defaults.plugins.tooltip.boxPadding = 6;
  Chart.defaults.maintainAspectRatio = false;

  /* ---------- Formatadores ---------- */
  function brl(v) {
    return 'R$ ' + Number(v || 0).toLocaleString('pt-BR', {
      minimumFractionDigits: 2, maximumFractionDigits: 2
    });
  }
  function num(v) {
    return Number(v || 0).toLocaleString('pt-BR');
  }
  function compact(v) {
    var n = Number(v || 0);
    if (Math.abs(n) >= 1000000) return (n / 1000000).toFixed(1).replace('.', ',') + 'M';
    if (Math.abs(n) >= 1000) return (n / 1000).toFixed(1).replace('.', ',') + 'k';
    return num(n);
  }

  function fmt(money) { return money ? brl : num; }

  /* ---------- Gradiente vertical para barras ---------- */
  function gradient(ctx, area, color) {
    if (!area) return color;
    var g = ctx.createLinearGradient(0, area.bottom, 0, area.top);
    g.addColorStop(0, hexA(color, 0.55));
    g.addColorStop(1, color);
    return g;
  }
  function hexA(hex, a) {
    var h = hex.replace('#', '');
    var r = parseInt(h.substring(0, 2), 16),
        gg = parseInt(h.substring(2, 4), 16),
        b = parseInt(h.substring(4, 6), 16);
    return 'rgba(' + r + ',' + gg + ',' + b + ',' + a + ')';
  }

  function colorFor(label, i) {
    var key = String(label || '').toLowerCase();
    return SEMANTIC[key] || PALETTE[i % PALETTE.length];
  }

  /* ---------- Plugin: texto no centro do doughnut ---------- */
  var centerText = {
    id: 'centerText',
    afterDraw: function (chart, args, opts) {
      if (!opts || !opts.text) return;
      var ctx = chart.ctx;
      var meta = chart.getDatasetMeta(0);
      if (!meta || !meta.data || !meta.data.length) return;
      var el = meta.data[0];
      var x = el.x, y = el.y;
      ctx.save();
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillStyle = '#0f172a';
      ctx.font = '700 22px ' + Chart.defaults.font.family;
      ctx.fillText(opts.text, x, y - 6);
      if (opts.label) {
        ctx.fillStyle = '#94a3b8';
        ctx.font = '600 11px ' + Chart.defaults.font.family;
        ctx.fillText(String(opts.label).toUpperCase(), x, y + 14);
      }
      ctx.restore();
    }
  };
  Chart.register(centerText);

  /* ---------- Vazio (sem dados) ---------- */
  function isEmpty(data) {
    return !data || !data.length || data.every(function (v) { return !Number(v); });
  }
  function drawEmpty(canvas, msg) {
    var ctx = canvas.getContext('2d');
    var w = canvas.clientWidth || canvas.width;
    var h = canvas.clientHeight || canvas.height || 180;
    ctx.clearRect(0, 0, w, h);
    ctx.save();
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#94a3b8';
    ctx.font = '600 13px ' + Chart.defaults.font.family;
    ctx.fillText(msg || 'Sem dados no período', w / 2, h / 2);
    ctx.restore();
  }

  function el(id) {
    var c = document.getElementById(id);
    if (!c) return null;
    return c;
  }

  var API = {
    palette: PALETTE,
    brl: brl,

    /* ---- Barras (agrupadas ou simples) ---- */
    bar: function (id, labels, series, opts) {
      var c = el(id); if (!c) return null;
      opts = opts || {};
      var f = fmt(opts.money);

      var allEmpty = series.every(function (s) { return isEmpty(s.data); });
      if (allEmpty) { drawEmpty(c, opts.emptyText); return null; }

      var datasets = series.map(function (s, i) {
        var color = s.color || colorFor(s.label, i);
        return {
          label: s.label,
          data: s.data,
          backgroundColor: function (ctx) {
            return gradient(ctx.chart.ctx, ctx.chart.chartArea, color);
          },
          hoverBackgroundColor: color,
          borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
          borderSkipped: false,
          maxBarThickness: opts.thickness || 42,
          categoryPercentage: 0.72,
          barPercentage: 0.9
        };
      });

      return new Chart(c, {
        type: 'bar',
        data: { labels: labels, datasets: datasets },
        options: {
          responsive: true,
          animation: { duration: 700, easing: 'easeOutQuart' },
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: series.length > 1 },
            tooltip: {
              callbacks: {
                label: function (ctx) { return ' ' + ctx.dataset.label + ': ' + f(ctx.parsed.y); }
              }
            }
          },
          scales: {
            x: {
              grid: { display: false, drawBorder: false },
              ticks: { font: { weight: '600' } }
            },
            y: {
              beginAtZero: true,
              border: { display: false },
              grid: { color: '#eef1f7', drawTicks: false },
              ticks: {
                padding: 8,
                callback: function (v) { return opts.money ? compact(v) : num(v); }
              }
            }
          }
        }
      });
    },

    /* ---- Barras horizontais ---- */
    barH: function (id, labels, data, opts) {
      var c = el(id); if (!c) return null;
      opts = opts || {};
      var f = fmt(opts.money);
      if (isEmpty(data)) { drawEmpty(c, opts.emptyText); return null; }

      var colors = labels.map(function (l, i) { return colorFor(l, i); });

      return new Chart(c, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: opts.label || '',
            data: data,
            backgroundColor: colors.map(function (x) { return hexA(x, .85); }),
            hoverBackgroundColor: colors,
            borderRadius: 8,
            borderSkipped: false,
            maxBarThickness: 26
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          animation: { duration: 700, easing: 'easeOutQuart' },
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: function (ctx) { return ' ' + f(ctx.parsed.x); } } }
          },
          scales: {
            x: {
              beginAtZero: true,
              border: { display: false },
              grid: { color: '#eef1f7', drawTicks: false },
              ticks: { callback: function (v) { return opts.money ? compact(v) : num(v); } }
            },
            y: { grid: { display: false, drawBorder: false }, ticks: { font: { weight: '600' } } }
          }
        }
      });
    },

    /* ---- Pizza ---- */
    pie: function (id, labels, data, opts) {
      return slice('pie', id, labels, data, opts);
    },

    /* ---- Rosca (com total no centro) ---- */
    doughnut: function (id, labels, data, opts) {
      return slice('doughnut', id, labels, data, opts);
    },

    /* ---- Linha (evolução) ---- */
    line: function (id, labels, series, opts) {
      var c = el(id); if (!c) return null;
      opts = opts || {};
      var f = fmt(opts.money);
      var allEmpty = series.every(function (s) { return isEmpty(s.data); });
      if (allEmpty) { drawEmpty(c, opts.emptyText); return null; }

      var datasets = series.map(function (s, i) {
        var color = s.color || colorFor(s.label, i);
        return {
          label: s.label,
          data: s.data,
          borderColor: color,
          backgroundColor: function (ctx) {
            var area = ctx.chart.chartArea;
            if (!area) return hexA(color, .12);
            var g = ctx.chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
            g.addColorStop(0, hexA(color, .28));
            g.addColorStop(1, hexA(color, .02));
            return g;
          },
          fill: true,
          tension: .38,
          borderWidth: 2.5,
          pointRadius: 0,
          pointHoverRadius: 5,
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: color,
          pointHoverBorderWidth: 2.5
        };
      });

      return new Chart(c, {
        type: 'line',
        data: { labels: labels, datasets: datasets },
        options: {
          responsive: true,
          animation: { duration: 800, easing: 'easeOutQuart' },
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: series.length > 1 },
            tooltip: { callbacks: { label: function (ctx) { return ' ' + ctx.dataset.label + ': ' + f(ctx.parsed.y); } } }
          },
          scales: {
            x: { grid: { display: false, drawBorder: false }, ticks: { font: { weight: '600' } } },
            y: {
              beginAtZero: true, border: { display: false },
              grid: { color: '#eef1f7', drawTicks: false },
              ticks: { padding: 8, callback: function (v) { return opts.money ? compact(v) : num(v); } }
            }
          }
        }
      });
    }
  };

  /* ---- implementação comum de pie/doughnut ---- */
  function slice(type, id, labels, data, opts) {
    var c = el(id); if (!c) return null;
    opts = opts || {};
    var f = fmt(opts.money);
    if (isEmpty(data)) { drawEmpty(c, opts.emptyText); return null; }

    var colors = labels.map(function (l, i) { return colorFor(l, i); });
    var total = data.reduce(function (a, b) { return a + Number(b || 0); }, 0);

    var cfg = {
      type: type,
      data: {
        labels: labels,
        datasets: [{
          data: data,
          backgroundColor: colors,
          borderColor: '#fff',
          borderWidth: 3,
          hoverOffset: 10,
          hoverBorderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        cutout: type === 'doughnut' ? '66%' : 0,
        animation: { animateRotate: true, duration: 800, easing: 'easeOutQuart' },
        plugins: {
          legend: { position: opts.legend || 'bottom' },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                var v = ctx.parsed;
                var pct = total ? (v / total * 100).toFixed(1).replace('.', ',') : '0';
                return ' ' + ctx.label + ': ' + f(v) + ' (' + pct + '%)';
              }
            }
          }
        }
      }
    };

    if (type === 'doughnut' && opts.center !== false) {
      cfg.options.plugins.centerText = {
        text: opts.center || (opts.money ? compact(total) : num(total)),
        label: opts.centerLabel || 'Total'
      };
    }

    return new Chart(c, cfg);
  }

  global.AppCharts = API;
})(window);
