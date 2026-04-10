Database export for easy setup.

File:
- db_antrian_infinix.sql

Import steps (XAMPP / phpMyAdmin):
1. Create database named db_antrian_infinix.
2. Open phpMyAdmin.
3. Select db_antrian_infinix database.
4. Open Import tab.
5. Choose database/exports/db_antrian_infinix.sql.
6. Click Import.

Import steps (CLI):
mysql -h 127.0.0.1 -P 3306 -u root db_antrian_infinix < database/exports/db_antrian_infinix.sql
