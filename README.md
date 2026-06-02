# CryptoInvestment Dashboard Pro

Prueba técnica desarrollada en Laravel y JavaScript para el seguimiento de activos digitales en tiempo real con persistencia de datos históricos.

## 🚀 Requisitos e Instalación

1. Clonar el repositorio.
2. Ejecutar `composer install` para descargar las dependencias.
3. Configurar las variables de entorno en el archivo `.env` (Base de datos y `CMC_API_KEY`).
4. Ejecutar las migraciones con `php artisan migrate`.
5. Iniciar el servidor local con `php artisan serve`.

---

## 📊 Documentación del Ejercicio (Tarea 1: Análisis RF - RNF)

### 1. Requisitos Funcionales (RF)
* **RF01 - Monitoreo en Tiempo Real:** El sistema debe consultar los precios actuales, variaciones porcentuales (24h) y volumen de mercado de los activos seleccionados de forma automática cada 30 segundos.
* **RF02 - Navegación sin Recarga (SPA):** Toda la interacción de la interfaz debe ejecutarse de forma asíncrona para evitar la recarga completa del sitio.
* **RF03 - Persistencia Histórica:** Cada consulta realizada a la API externa debe quedar registrada en la base de datos local para asegurar la trazabilidad del valor en el tiempo.
* **RF04 - Filtro de Línea de Tiempo:** El usuario debe poder seleccionar un activo y un rango de fechas para graficar la evolución del precio.

## 2. Requisitos No Funcionales (RNF)
* **RNF01 - Diseño Responsivo:** La interfaz debe ser adaptable mediante CSS (Bootstrap 5) para garantizar una correcta visualización en dispositivos móviles y de escritorio.
* **RNF02 - Rendimiento y Consumo Eficiente de API:** Uso de un servicio centralizado en el Backend para mitigar la sobreexposición de la API Key y controlar las cuotas del plan gratuito.
* **RNF03 - Arquitectura Desacoplada (Clean Code):** Separación de responsabilidades mediante la creación de un `CoinMarketCapService` dedicado, aislando la lógica de negocio de los controladores.

## 3. Modelo de Datos (Diagrama Entidad-Relación)
La persistencia de datos se estructuró mediante una relación de uno a muchos ($1:N$) utilizando migraciones de Laravel Eloquent:

* **Tabla: `cryptocurrencies`** (Almacena las monedas de seguimiento activo)
  - `id` (BIGINT, PK, Autoincremental)
  - `cmc_id` (BIGINT, Unique) -> ID asignado por CoinMarketCap
  - `name` (VARCHAR)
  - `symbol` (VARCHAR)
  - `slug` (VARCHAR)

* **Tabla: `price_histories`** (Registra la variación del mercado para el gráfico)
  - `id` (BIGINT, PK, Autoincremental)
  - `cryptocurrency_id` (BIGINT, FK -> cryptocurrencies.id)
  - `price` (DECIMAL 16,8) -> Alta precisión para activos volátiles
  - `percent_change_24h` (DECIMAL 8,2)
  - `volume_24h` (DECIMAL 16,2)
  - `recorded_at` (TIMESTAMP) -> Fecha y hora exacta del muestreo