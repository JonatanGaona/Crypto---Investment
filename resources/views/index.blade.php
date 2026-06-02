<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoInvestment Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .crypto-card { transition: transform 0.2s; }
        .crypto-card:hover { transform: translateY(-3px); }
        .text-up { color: #2ecc71; font-weight: bold; }
        .text-down { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-chart-line text-warning me-2"></i>CryptoInvestment Panel</span>
            <span class="badge bg-secondary" id="update-timer">Actualizando en: 30s</span>
        </div>
    </nav>

    <div class="container">
        <div class="row text-center mb-4" id="crypto-cards-container">
            <div class="col-12 text-center p-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Cargando datos del mercado en vivo...</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card p-4 mb-4">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Verificación de Líneas de Tiempo Históricas</h5>
                    
                    <div class="row g-3 align-items-center mb-4">
                        <div class="col-md-4">
                            <label class="form-label font-weight-bold">Seleccionar Criptomoneda</label>
                            <select class="form-select" id="crypto-select">
                                <option value="1">Bitcoin (BTC)</option>
                                <option value="2">Ethereum (ETH)</option>
                                <option value="3">Binance Coin (BNB)</option>
                                <option value="4">Solana (SOL)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Desde</label>
                            <input type="date" class="form-control" id="date-from">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hasta</label>
                            <input type="date" class="form-control" id="date-to">
                        </div>
                        <div class="col-md-2 mt-md-4 pt-md-2">
                            <button class="btn btn-primary w-100" id="btn-filter"><i class="fa-solid fa-filter me-2"></i>Filtrar</button>
                        </div>
                    </div>

                    <div style="position: relative; height:40vh; width:100%">
                        <canvas id="historyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Variables globales para el control de la SPA
        let countdown = 30;
        let chartInstance = null;

        // 1. CARGA INICIAL Y ACTUALIZACIÓN PERIÓDICA (Fetch API sin recargar)
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
            container.innerHTML = ''; // Limpiar el spinner o datos viejos

            cryptos.forEach(crypto => {
                const isPositive = crypto.change >= 0;
                const changeClass = isPositive ? 'text-up' : 'text-down';
                const arrowIcon = isPositive ? 'fa-caret-up' : 'fa-caret-down';

                container.innerHTML += `
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card p-3 crypto-card">
                            <h6 class="text-muted mb-1">${crypto.name} (${crypto.symbol})</h6>
                            <h3 class="fw-bold text-dark">$${crypto.price.toLocaleString('en-US', {minimumFractionDigits: 2})}</h3>
                            <p class="mb-2 ${changeClass}">
                                <i class="fa-solid ${arrowIcon} me-1"></i>${crypto.change}% (24h)
                            </p>
                            <small class="text-muted d-block">Vol: $${crypto.volume.toLocaleString('en-US')}</small>
                        </div>
                    </div>
                `;
            });
        }

        // Timer de cuenta regresiva para la actualización en tiempo real
        setInterval(() => {
            countdown--;
            document.getElementById('update-timer').innerText = `Actualizando en: ${countdown}s`;
            if (countdown <= 0) {
                fetchMarketData();
                countdown = 30;
            }
        }, 1000);

        // 2. INICIALIZACIÓN DE GRÁFICO (Chart.js)
        function initChart(labels = [], prices = []) {
            const ctx = document.getElementById('historyChart').getContext('2d');
            
            if (chartInstance) {
                chartInstance.destroy(); // Destruye el gráfico viejo antes de pintar el nuevo filtrado
            }

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Precio Histórico (USD)',
                        data: prices,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: false }
                    }
                }
            });
        }

        // Mock para simular línea de tiempo inicial (Fines prácticos en la primera carga)
        function loadInitialChart() {
            const mockLabels = ['09:00 AM', '09:15 AM', '09:30 AM', '09:45 AM', '10:00 AM'];
            const mockPrices = [67200, 67250, 67180, 67310, 67450];
            initChart(mockLabels, mockPrices);
        }

        // Al cargar la SPA por primera vez
        document.addEventListener('DOMContentLoaded', () => {
            fetchMarketData();
            loadInitialChart();
            
            // Establecer fechas por defecto en los inputs (Hoy)
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date-from').value = today;
            document.getElementById('date-to').value = today;
        });

        // Evento de filtrado para simular las líneas de tiempo requeridas
        document.getElementById('btn-filter').addEventListener('click', async () => {
            const cryptoId = document.getElementById('crypto-select').value;
            const from = document.getElementById('date-from').value;
            const to = document.getElementById('date-to').value;

            try {
                const response = await fetch(`/api/crypto/history?crypto_id=${cryptoId}&from=${from}&to=${to}`);
                const data = await response.json();

                if (data.labels.length > 0) {
                    // Pintamos la gráfica con los datos reales de nuestra BD
                    initChart(data.labels, data.prices);
                } else {
                    alert("No hay registros históricos en este rango de fechas para la moneda seleccionada. Sigue acumulando datos en vivo o amplía el rango.");
                }
            } catch (error) {
                console.error("Error al filtrar el historial:", error);
            }
        });
    </script>
</body>
</html>