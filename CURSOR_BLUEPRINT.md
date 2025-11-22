BLUEPRINT: ERP Lite (Ventas, Inventario y Marketing)

Stack: Laravel 9.19 + Vue 3 (Composition API) + Tailwind CSS + MySQL.

1. Reglas Generales de Desarrollo

Diseño Mobile-First: Todas las vistas deben priorizar pantallas pequeñas.

Tablas Responsivas: En móvil, las filas de datos deben verse como "Tarjetas" (Cards). En escritorio como filas de tabla.

Server Side Processing: Todas las listas (Ventas, Clientes, Inventario) deben paginarse desde el backend.

Estilos: Usar Tailwind CSS. Implementar Dark Mode usando la clase dark: y persistencia en localStorage.

Transacciones: Toda operación que toque dos tablas (ej: Venta + Stock) debe usar DB::transaction.

2. Base de Datos (Schema)

A. Users

Tabla existente. Agregar columna role (enum: 'admin', 'seller') default 'seller'.

B. Clients

id, name (string), phone (string, index - vital para WhatsApp), email (nullable).

last_purchase_date (date, nullable): Se actualiza autom. con cada compra.

total_spent (decimal): Se actualiza autom. con cada compra.

timestamps.

C. Products

id, name, code (nullable, unique), cost_price (decimal), sale_price (decimal).

stock (integer), min_stock (integer, default 5).

image_path (nullable), is_active (boolean, default true).

timestamps.

D. Sales (Cabecera)

id, client_id (FK), user_id (FK).

total_amount (decimal).

status (enum: 'PAID', 'PENDING', 'CANCELED').

payment_method (enum: 'CASH', 'CARD', 'YAPE', 'PLIN', 'CREDIT').

sale_date (datetime).

timestamps.

E. SaleDetails (Detalle)

id, sale_id (FK), product_id (FK).

quantity (int), unit_price (decimal, precio histórico), subtotal (decimal).

F. InventoryMovements (Kardex)

id, product_id (FK), type (enum: 'IN', 'OUT').

quantity (int), reason (string: 'SALE', 'PURCHASE', 'ADJUSTMENT', 'RETURN').

related_sale_id (nullable, FK sales).

timestamps.

3. Lógica de Negocio (Backend)

Módulo de Ventas (Store Method)

Recibir datos: Cliente (ID o datos nuevos), Lista de Productos (ID, Cantidad), Método Pago.

Dentro de DB::Transaction:

Si el cliente no tiene ID, crearlo primero.

Crear Sale.

Iterar productos:

Verificar Stock suficiente.

Crear SaleDetail.

Restar Product->stock.

Crear InventoryMovement (Type: OUT, Reason: SALE).

Actualizar Client: last_purchase_date = now(), total_spent += total.

Retornar JSON con link de WhatsApp generado:

Formato: whatsapp://send/?phone=51[CELULAR]&text=Hola [NOMBRE], tu compra de [MONTO] se procesó exitosamente.

Módulo de Cobros

Endpoint para listar ventas con status = 'PENDING'.

Acción "Marcar Pagado": Cambia status a 'PAID' y registra fecha.

Acción "Enviar Cobro": Link WhatsApp: whatsapp://send/?phone=...&text=Hola [NOMBRE], tienes una deuda de [MONTO] del día [FECHA].

Módulo de Campañas (Filtros Avanzados)

Endpoint ClientController@index con filtros:

inactive_30d: Clientes con last_purchase_date < now()->subDays(30).

recent_7d: Clientes con last_purchase_date > now()->subDays(7).

vip: Clientes con total_spent > 1000.

4. Requerimientos UI (Frontend Vue)

Componentes Globales

AppSidebar: Con toggle Dark/Light mode y navegación (Ventas, Cobros, Inventario, Reportes, Marketing).

ServerSideTable: Componente reutilizable. Props: apiUrl, columns. Emite eventos de filtro. Debe renderizar <table> en desktop y <div> con grid en móvil.

Vistas Específicas

POS (Ventas):

Autocomplete para Cliente (Busca o permite crear).

Buscador de productos (Click agrega a lista temporal).

Resumen de total en tiempo real.

Inventario:

Indicador visual (rojo) si stock <= min_stock.

Botón para ver historial (Kardex) por producto.