<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoInvestment | Dashboard Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #0f172a; /* Fondo oscuro moderno (Slate 900) */
            color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .navbar {
            background-color: #1e293b !important; /* Slate 800 */
            border-bottom: 1px solid #334155;
        }
        .card-custom {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        .card-custom:hover {
            transform: translateY(-4px);
            border-color: #6366f1; /* Efecto neón sutil */
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.15);
        }
        .text-up { color: #10b981; font-weight: 600; } /* Verde Esmeralda moderno */
        .text-down { color: #f43f5e; font-weight: 600; } /* Rosado/Rojo moderno */
        .form-control, .form-select {
            background-color: #0f172a;
            border: 1px solid #334155;
            color: #f8fafc;
        }
        .form-control:focus, .form-select:focus {
            background-color: #0f172a;
            border-color: #6366f1;
            color: #f8fafc;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: white;
            font-weight: 500;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
        }
        .card-custom h5 {
            color: #ffffff !important;
        }
        .card-custom .text-muted, .form-label {
            color: #94a3b8 !important; /* Un gris claro muy elegante (Slate 400) */
        }
        #crypto-cards-container .text-muted {
            color: #cbd5e1 !important; /* Gris claro para los nombres de las criptos arriba del precio */
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark mb-4 py-3">
        <div class="container">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-cubes text-warning me-2 fs-3"></i>
                <span class="fw-bold tracking-tight">CryptoInvestment</span>
                <span class="badge bg-primary ms-3 font-monospace fs-7">v2.0 Pro</span>
            </span>
            <span class="badge bg-dark border border-secondary px-3 py-2" id="update-timer">
                <i class="fa-solid fa-arrows-rotate fa-spin text-info me-2"></i>Sincronizando en: 30s
            </span>
        </div>
    </nav>

    <div class="container">
        
        <div class="d-flex align-items-center mb-3">
            <h5 class="text-uppercase tracking-wider text-muted small mb-0 me-2">Monedas en Seguimiento Activo</h5>
            <hr class="flex-grow-1 border-secondary opacity-25">
        </div>

        <div class="row g-3 mb-5" id="crypto-cards-container">
            <div class="col-12 text-center p-5 card-custom">
                <div class="spinner-border text-indigo" role="status" style="color: #6366f1;"></div>
                <p class="mt-3 text-muted">Conectando con los nodos de CoinMarketCap...</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-custom p-4 mb-5">
                    <div class="d-flex align-items-center mb-4">
                        <div class="p-2 bg-dark rounded-3 me-3 border border-secondary">
                            <i class="fa-solid fa-chart-area text-indigo fs-4" style="color: #6366f1;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Verificación de Líneas de Tiempo</h5>
                            <small class="text-muted">Persistencia de datos históricos local de transacciones</small>
                        </div>
                    </div>
                    
                    <div class="row g-3 align-items-end mb-4 bg-dark p-3 rounded-3 border border-secondary">
                        <div class="col-lg-4 col-md-12">
                            <label class="form-label small text-muted fw-semibold">Activo Digital</label>
                            <select class="form-select" id="crypto-select">
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small text-muted fw-semibold">Rango Desde</label>
                            <input type="date" class="form-control" id="date-from">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small text-muted fw-semibold">Rango Hasta</label>
                            <input type="date" class="form-control" id="date-to">
                        </div>
                        <div class="col-lg-2 col-md-12">
                            <button class="btn btn-gradient w-100 py-2" id="btn-filter">
                                <i class="fa-solid fa-magnifying-glass-chart me-2"></i>Analizar
                            </button>
                        </div>
                    </div>

                    <div class="bg-dark p-3 rounded-3 border border-secondary" style="position: relative; height:45vh; width:100%">
                        <canvas id="historyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bundle/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let countdown = 30;
        let chartInstance = null;

        async function fetchMarketData() {
            try {
                const response = await fetch('/api/crypto/updates');
                const data = await response.json();
                
                if (response.ok) {
                    renderCryptoCards(data);
                }
            } catch (error) {
                console.error("Error cargando datos del backend:", error);
            }
        }

        function renderCryptoCards(cryptos) {
            const container = document.getElementById('crypto-cards-container');
            const select = document.getElementById('crypto-select');
            const currentSelection = select.value;

            container.innerHTML = '';
            select.innerHTML = '';

            cryptos.forEach(crypto => {
                const isPositive = crypto.change >= 0;
                const changeClass = isPositive ? 'text-up' : 'text-down';
                const arrowIcon = isPositive ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                const borderBadge = isPositive ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';

                container.innerHTML += `
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card card-custom p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-bold">${crypto.name}</span>
                                <span class="badge ${borderBadge} font-monospace">${crypto.symbol}</span>
                            </div>
                            <h3 class="fw-bold tracking-tight mb-2 text-white">$${crypto.price.toLocaleString('en-US', {minimumFractionDigits: 2})}</h3>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="${changeClass} small d-flex align-items-center">
                                    <i class="fa-solid ${arrowIcon} me-1"></i>${crypto.change}%
                                </span>
                                <span class="text-muted style-vol" style="font-size: 0.75rem;">Vol: $${crypto.volume.toLocaleString('en-US', {maximumFractionDigits: 0})}</span>
                            </div>
                        </div>
                    </div>
                `;

                select.innerHTML += `<option value="${crypto.id}">${crypto.name} (${crypto.symbol})</option>`;
            });

            if (currentSelection) {
                select.value = currentSelection;
            }
        }

        setInterval(() => {
            countdown--;
            document.getElementById('update-timer').innerHTML = `<i class="fa-solid fa-arrows-rotate fa-spin text-info me-2"></i>Sincronizando en: ${countdown}s`;
            if (countdown <= 0) {
                fetchMarketData();
                countdown = 30;
            }
        }, 1000);

        function initChart(labels = [], prices = []) {
            const ctx = document.getElementById('historyChart').getContext('2d');
            
            if (chartInstance) {
                chartInstance.destroy();
            }

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Historial de Cotización (USD)',
                        data: prices,
                        borderColor: '#6366f1', /* Color Indigo moderno */
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#6366f1',
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#94a3b8' } }
                    },
                    scales: {
                        x: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' } },
                        y: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' }, beginAtZero: false }
                    }
                }
            });
        }

        async function loadRealChartData() {
            const cryptoId = document.getElementById('crypto-select').value;
            const from = document.getElementById('date-from').value;
            const to = document.getElementById('date-to').value;

            try {
                const response = await fetch(`/api/crypto/history?crypto_id=${cryptoId}&from=${from}&to=${to}`);
                const data = await response.json();

                if (data.labels && data.labels.length > 0) {
                    initChart(data.labels, data.prices);
                } else {
                    initChart(['Sin datos'], [0]);
                }
            } catch (error) {
                console.error("Error al cargar gráfico inicial:", error);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date-from').value = today;
            document.getElementById('date-to').value = today;

            fetchMarketData();
            loadRealChartData();
        });

        document.getElementById('btn-filter').addEventListener('click', async () => {
            const cryptoId = document.getElementById('crypto-select').value;
            const from = document.getElementById('date-from').value;
            const to = document.getElementById('date-to').value;

            try {
                const response = await fetch(`/api/crypto/history?crypto_id=${cryptoId}&from=${from}&to=${to}`);
                const data = await response.json();

                if (data.labels && data.labels.length > 0) {
                    initChart(data.labels, data.prices);
                } else {
                    alert("No hay registros guardados en BD local para este rango aún. Deja la app abierta unos minutos para acumular historial.");
                }
            } catch (error) {
                console.error("Error al filtrar el historial:", error);
            }
        });
    </script>
</body>
</html>