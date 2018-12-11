D:\xampp\mysql\bin\mysqldump -d --comments=FALSE -u root wd_purchase_code  > 1_schema.sql
D:\xampp\mysql\bin\mysqldump -t --order-by-primary --comments=FALSE -u root wd_purchase_code  > 2_init_data.sql
