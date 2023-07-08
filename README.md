# PART 1

## Setup Postgresql database

```bash
docker-compose up -d
```

## Migrate database

```bash
php artisan migrate
```

## Run api

```bash
php artisan serve
```

---

# PART 2

**1. ให้อธิบายว่าจะใช้วิธีการอะไรได้บ้างในการป้องกัน brute force attack หรือเดารหัสผ่านใน login form**

> การ Brute force attack อาจจะเกิดขึ้นได้ทั้ง Front-end และ Back-end โดยตรง เราจึงควรมีการป้องกันทั้ง 2 ส่วนโดยที่

-   Front-end ควรใช้เครื่องมือเหล่านี้ในการป้องกัน

    -   2FA หรือ Two Factor Authentication

        > โดยพื้นฐาน ระบบจะมี passwod ของ user เป็น factor ที่ 1 ในการยืนยันตัวตนเข้าระบบ และมีการใช้รหัสอีกชุดที่เป็น factor ตัวที่ 2 เพื่อใช้ในการยืนยันตัวตนเข้าระบบเพิ่มขึ้นมาจากเดิม

        > ex. SMS-OTP, Email-OTP, Google Authenticator เป็นต้น

    -   Capcha หรือ Re-capcha
        > เป็นการทำโจทย์เล็กๆน้อยๆที่คอมพิวเตอร์ทั่วไปทำไม่ได้ เพื่อเป็นการกรอง submit ที่มาจากคนจริงๆ ช่วยให้ระบบสามารถป้องกัน brute force scripting หรือ automate brute force ได้

-   Back-end ควรใช้เครื่องมือเหล่านี้ในการป้องกัน
    -   Rate Limiting [IP, Account lockout]
        > เมื่อมีการพยายามยิง request มาที่ ระบบ authentication ระบบจะทำการ track จำนวนครั้งที่ user หรือ IP นั้นๆ ใช้ creadentail login ผิดพลาด เพื่อเมื่อถึง threshold ที่ตั้งไว้ เช่น login ผิด 5 ครั้ง จะทำการ Block user หรือ IP นั้นๆถาวร/ชั่วคราวโดยมีระยะเวลาเท่าไหรตามแต่ policy ของบริษัท
    -   Logging & Monitoring
        > ทำ log การ request login เพื่อตรวจจับ request ที่น่าสงสัย เช่น มีการพยายาม login หลาย request จาก IP เดิม เป็นต้น

**Note** : ในมุมมองส่วนตัวนั้น การทำระบบให้มีความปลอดภัยนั้นเป็นสิ่งที่ดี แต่ควรพึงระลึกไว้เสมอว่ามันเป็นการ trade of ระหว่างความปลอดภัยกับ user experience ดังนั้นเราควรวางระบบให้ balance กันทั้ง 2 ฝั่ง

---

**2. จงเขียนตัวอย่าง sql query ในโค้ด php โดยให้มีชุดคำสั่งที่ช่วยป้องกัน sql injection (ตั้งชื่อตารางชื่อฟิลด์ด้วยตัวเองได้เลย)**

> มีการป้องกัน sql injection ด้วยการ sanitization input ของ user ก่อนที่จะนำไป query โดยการทำ 
> - prepare-statement
> - bind-parameters
> 
```php
<?php
$condb = new mysqli("localhost","username","password","database");

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username = ? AND password = ?";
$statement = $condb->prepare($sql);
$statement->bind_param("ss", $username, $password);
$statement->execute();

$result = $statement->get_result();

if ($row = $result->fetch_assoc()) {
    echo "Login successful!";
} else {
    echo "Invalid credentials.";
}

$statement->close();
>
```

---

**3. จงเขียน sql query ที่มี sub query ในตำแหน่งต่างๆ อย่างน้อยสองแบบ (ตั้งชื่อตารางชื่อฟิลด์ด้วยตัวเองได้เลย)**

> Create seed data

```sql
-- Create "Employees" Table
CREATE TABLE Employees (
    ID INT PRIMARY KEY,
    Name VARCHAR(50),
    DepartmentID INT
);

-- Insert Seed Data into "Employees" Table
INSERT INTO Employees (ID, Name, DepartmentID)
VALUES
    (1, 'John', 1),
    (2, 'Alice', 2),
    (3, 'David', 1),
    (4, 'Emma', 2);

-- Create "Departments" Table
CREATE TABLE Departments (
    ID INT PRIMARY KEY,
    Name VARCHAR(50)
);

-- Insert Seed Data into "Departments" Table
INSERT INTO Departments (ID, Name)
VALUES
    (1, 'Sales'),
    (2, 'Finance');

-- Create "Salaries" Table
CREATE TABLE Salaries (
    EmployeeID INT,
    Salary INT
);

-- Insert Seed Data into "Salaries" Table
INSERT INTO Salaries (EmployeeID, Salary)
VALUES
    (1, 5000),
    (2, 6000),
    (3, 4500),
    (4, 7000);
```

Employees table:
ID | Name | DepartmentID
----|--------|--------------
1 | John | 1
2 | Alice | 2
3 | David | 1
4 | Emma | 2

Departments table:
ID | Name
----|------
1 | Sales
2 | Finance

Salaries table:
EmployeeID | Salary
-----------|-------
1 | 5000
2 | 6000
3 | 4500
4 | 7000

> คำสั่งเรียกข้อมูลของพนักงานที่มีรายได้มากที่สุดของแต่ละแผนกออกมาแสดง ด้วยการใช้
> - Main query เพื่อเตรียมชุดข้อมูลและตารางที่จะดึงข้อมูล และ filter ด้วย ```WHERE (...)``` 
> - Middle sub query เพื่อดึงเงินเดือนที่มากที่สุด ```MAX(Salary)``` ของแต่ละแผนกออกมาตาม id ของพนักงานใน ```IN (...)```
> - Inner sub query เพื่อจับกลุ่มพนักงานที่อยู่แผนกเดียวกันให้ middle query ทำงานใน ```IN (...) ```

```sql
-- SQL query statement --

-- Main --
SELECT E.Name, D.Name AS Department, S.Salary
FROM Employees E
JOIN Departments D ON E.DepartmentID = D.ID
JOIN Salaries S ON E.ID = S.EmployeeID
WHERE S.Salary = (
    -- Middle --
    SELECT MAX(Salary)
    FROM Salaries
    WHERE EmployeeID IN (
        -- Inner --
        SELECT ID
        FROM Employees
        WHERE DepartmentID = E.DepartmentID
    )
);
```

Result should be
Name | Department | Salary
-------|------------|--------
Alice | Finance | 7000
John | Sales | 5000
---
**4. จากตาราง tb_product(id,name,status,shop_id) และ tb_shop(id,name) จงเขียน โค้ด select เพื่อแสดงสินค้าของร้าน ที่มีชื่อร้าน "rudy shop"**

> Create seed data 

```sql
-- Create tb_shop table
CREATE TABLE tb_shop (
  id INT PRIMARY KEY,
  name VARCHAR(255)
);

-- Create tb_product table
CREATE TABLE tb_product (
  id INT PRIMARY KEY,
  name VARCHAR(255),
  status INT,
  shop_id INT,
  FOREIGN KEY (shop_id) REFERENCES tb_shop(id)
);

-- Insert seed data into tb_shop table
INSERT INTO tb_shop (id, name)
VALUES
  (1, 'rudy shop'),
  (2, '7-11'),
  (3, 'lotus');

-- Insert seed data into tb_product table
INSERT INTO tb_product (id, name, status, shop_id)
VALUES
  (1, 'Macbook M2 PRO MAX', 1, 1),
  (2, 'Airtag', 0, 1),
  (3, 'Iphone 15 PRO MAX', 1, 1),
  (4, 'Bento', 1, 2),
  (5, 'Samyang', 0, 3),
  (6, 'Testo', 1, 3);
```

tb_shop:
id | name | status | shop_id
----|--------|--------------|------
1 | Macbook M2 PRO MAX | 1 | 1
2 | Airtag | 0 | 1
3 | Iphone 15 PRO MAX | 1 | 1
4 | Bento | 1 | 2
5 | Samyang | 0 | 3
6 | Testo | 1 | 3

Departments table:
id | name
----|------
1 | rudy shop
2 | 7-11
3 | lotus

> เรียกข้อมูลเพื่อแสดงสินค้าของร้าน ที่มีชื่อร้าน "rudy shop"

```sql
-- SQL query statement --
SELECT P.id AS prduct_id, P.name AS product_name
FROM tb_product P
JOIN tb_shop S ON S.id = P.shop_id
WHERE S.name = 'rudy shop';
```

Result should be
prduct_id | product_name
-------|------------
1 | Macbook M2 PRO MAX
2 | Airtag
3 | Iphone 15 PRO MAX
---
**5. เขียนคำสั่ง update สินค้าทุกตัวของร้าน "rudy shop" ให้มี status='0'**

> จากข้อมูลข้อที่ 4 สามารถเขียน query statement ได้ดังนี้

```sql
UPDATE tb_product
SET status = 0
WHERE shop_id = (SELECT id FROM tb_shop WHERE name = 'rudy shop');
```

> ดึงข้อมูลสินค้ามาแสดงเพื่อดูสถานะของสินค้าที่เปลี่ยนไป

```sql
SELECT * from tb_product;
```
---
**6. จงเขียน function ของ code sql เพื่อปรับรูปแบบการ select ข้อมูล ตามประเภทข้อมูลดังนี้เพื่อให้ได้ผลลัพธืดังตัวอย่าง type date ให้แสดงผลเป็น dd/mm/YYYY type float,double ให้แสดงผลเป็น x,xxx,xxx.xx (สามารถเขียนได้มากกว่า 2 ข้อที่ยกตัวอย่าง)**

> Create seed data 

```sql
-- Create the Order table
CREATE TABLE `Order` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `amount` DECIMAL(10, 2),
  `date` DATE,
  `time` TIME
);

-- Insert seed data into the Order table
INSERT INTO `Order` (`amount`, `date`, `time`)
VALUES
  (1056870.55, '2023-07-08', '14:30:00'),
  (25525.75, '2023-07-08', '15:45:00'),
  (506000.25, '2023-07-09', '09:15:00'),
  (154856.80, '2023-07-09', '12:00:00');
```

Order table:
id | amount | date | time
----|--------|--------------|-----
1 | 1056870.55 | 2023-07-08 | 14:30:00
2 | 25525.75 | 2023-07-08 | 15:45:00
3 | 506000.25 | 2023-07-09 | 09:15:00
4 | 154856.80 | 2023-07-09 | 12:00:00

> ดึงข้อมูลวันในรูปแบบ dd/mm/yyyy และดึงราคารูปแบบ comma-separated

```sql
-- SQL query statement --
SELECT
  DATE_FORMAT(`date`, '%d/%m/%Y') AS formatted_date,
  FORMAT(`amount`, 2) AS formatted_amount
FROM
  `Order`;
```
> Result should be

formatted_date | formatted_amount
-----|-----
08/07/2023 | 1,056,870.55
08/07/2023 | 25,525.75
09/07/2023 | 506,000.25
09/07/2023 | 154,856.80
---
**7. จงเขียน code function php ในการคำนวณผลลัพธ์ใบเสนอราคาโดยหัวข้อมีดังนี้**

-   ราคาสินค้ารวม = สามารถตั้งเองได้ ```-> $total```
-   ส่วนลดรวม = สามารถตั้งเองได้ ```-> $discount```
-   ราคาสินค้าหลังส่วนลด ```-> ($total - $discount)```
-   ภาษีมูลค่าเพิ่ม 7 % ```-> $tax```
-   ราคารวมสุทธิ ```-> $formattedAmount```
  
>PHP snippet
```php
<?php
$total = 5432.50;
$discount = 300.00;
$tax = 0.07;

$amount = ($total - $discount) * (1 + $tax);

$formattedAmount = number_format($amount, 2);

echo $formattedAmount;
?>
```
---
### Thank you for your consideration.
### Look forward to working together 🤝
## Best regards