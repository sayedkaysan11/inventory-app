# ERD - Inventory Management System

```mermaid
erDiagram
    users ||--o| user_profiles : "1-1 (FK + UNIQUE)"
    users ||--o{ stock_movements : "mencatat"
    categories ||--o{ products : "1-N"
    products ||--o{ stock_movements : "histori"
    products ||--o{ product_suppliers : ""
    suppliers ||--o{ product_suppliers : ""

    users { int id PK
            varchar username UK
            varchar password_hash
            enum role }
    user_profiles { int id PK
                    int user_id FK_UK
                    varchar full_name }
    categories { int id PK
                 varchar name UK }
    suppliers { int id PK
                varchar name
                varchar phone }
    products { int id PK
               int category_id FK
               varchar sku UK
               varchar name
               decimal price
               int stock
               int minimum_stock }
    product_suppliers { int product_id PK_FK
                        int supplier_id PK_FK
                        decimal supplier_price }
    stock_movements { int id PK
                      int product_id FK
                      int user_id FK
                      enum type
                      int quantity
                      varchar note
                      timestamp created_at }
```

| Relasi | Jenis | Implementasi |
|---|---|---|
| users - user_profiles | One-to-one | `user_profiles.user_id` FK + UNIQUE |
| categories - products | One-to-many | `products.category_id` FK, `ON DELETE RESTRICT` |
| products - suppliers | Many-to-many | junction `product_suppliers`, composite PK |
| products - stock_movements | One-to-many | FK `RESTRICT` (histori tidak boleh hilang) |
| users - stock_movements | One-to-many | FK `RESTRICT` (siapa yang melakukan transaksi) |
