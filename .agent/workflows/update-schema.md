---
description: How to update the database schema documentation when new migrations are added.
---

# Update Database Schema Documentation

Whenever you add a new table or modify an existing table via a migration, follow these steps to keep `DATABASE_SCHEMA.md` up to date:

1. **Locate New Migrations**: Check `database/migrations` for the latest migration files.
2. **identify Changes**:
   - If a new table is created (`Schema::create`), add it to the list in `DATABASE_SCHEMA.md`.
   - If an existing table is modified (`Schema::table`), update the corresponding table attributes or add new ones.
3. **Update `DATABASE_SCHEMA.md`**:
   - Maintain the markdown table format for consistency.
   - Include the attribute name, data type (e.g., String, BigInt, Decimal), and any specific details (e.g., Primary Key, Indexed, Nullable).
4. **Verify**: Ensure the documentation accurately reflects the current state of the database migrations.
