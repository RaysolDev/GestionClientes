# ServerSideTable Component

Componente reutilizable para tablas con paginación, ordenamiento y búsqueda del lado del servidor.

## Características

- ✅ Paginación del lado del servidor
- ✅ Ordenamiento por columnas
- ✅ Búsqueda con debounce
- ✅ Vista responsive: Tabla en desktop, Cards en móvil
- ✅ Slots scoped para personalización
- ✅ Dark mode compatible
- ✅ Loading y error states

## Props

| Prop | Tipo | Default | Descripción |
|------|------|---------|-------------|
| `url` | String | **requerido** | URL del endpoint API |
| `columns` | Array | **requerido** | Array de objetos `{key, label}` |
| `searchParam` | String | `'search'` | Nombre del parámetro de búsqueda |
| `sortParam` | String | `'sort'` | Nombre del parámetro de ordenamiento |
| `orderParam` | String | `'order'` | Nombre del parámetro de dirección (asc/desc) |
| `pageParam` | String | `'page'` | Nombre del parámetro de página |
| `perPageParam` | String | `'per_page'` | Nombre del parámetro de items por página |
| `perPage` | Number | `15` | Items por página |
| `initialSort` | String | `null` | Columna inicial para ordenar |
| `initialOrder` | String | `'asc'` | Dirección inicial (asc/desc) |
| `additionalParams` | Object | `{}` | Parámetros adicionales para la petición |

## Slots

### `cell-{columnKey}`
Slot scoped para personalizar el contenido de una celda específica.

**Props:**
- `row`: Objeto completo de la fila
- `value`: Valor de la celda

### `card`
Slot scoped para personalizar completamente la vista de cards en móvil.

**Props:**
- `row`: Objeto completo de la fila

## Ejemplo de Uso

```vue
<template>
  <ServerSideTable
    :url="'/api/clients'"
    :columns="columns"
    :additional-params="{ vip: true }"
  >
    <!-- Personalizar celda de nombre -->
    <template #cell-name="{ row, value }">
      <span class="font-semibold">{{ value }}</span>
    </template>

    <!-- Personalizar celda de total_spent -->
    <template #cell-total_spent="{ value }">
      <span class="text-green-600">S/ {{ value }}</span>
    </template>

    <!-- Personalizar vista de card en móvil -->
    <template #card="{ row }">
      <div class="space-y-2">
        <h3 class="font-bold">{{ row.name }}</h3>
        <p class="text-sm text-gray-600">{{ row.phone }}</p>
        <p class="text-lg font-semibold text-green-600">S/ {{ row.total_spent }}</p>
      </div>
    </template>
  </ServerSideTable>
</template>

<script setup>
const columns = [
  { key: 'name', label: 'Nombre' },
  { key: 'phone', label: 'Teléfono' },
  { key: 'email', label: 'Email' },
  { key: 'total_spent', label: 'Total Gastado' }
];
</script>
```

## Respuesta Esperada del API

El componente espera una respuesta de Laravel con paginación:

```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 15,
  "total": 100,
  "from": 1,
  "to": 15
}
```

O un array simple para respuestas sin paginación:

```json
[...]
```

