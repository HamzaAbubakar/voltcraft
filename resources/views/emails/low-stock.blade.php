# Low Stock Alert

The following inventory item has fallen to or below its reorder point:

- **SKU:** {{ $itemSku }}
- **Item:** {{ $itemName }}
- **Quantity on Hand:** {{ $quantity }}
- **Reorder Point:** {{ $reorderPoint }}

Please restock this item to avoid stockouts.

{{ config('app.name', 'Voltcraft ERP') }}