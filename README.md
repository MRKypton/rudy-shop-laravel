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

> 1. ให้อธิบายว่าจะใช้วิธีการอะไรได้บ้างในการป้องกัน brute force attack หรือเดารหัสผ่านใน login form

การ Brute force attack อาจจะเกิดขึ้นได้ทั้ง Front-end และ Back-end โดยตรง
เราจึงควรมีการป้องกันทั้ง 2 ส่วนโดยที่

-   Front-end ควรใช้เครื่องมือเหล่านี้ในการป้องกัน
    -   2FA หรือ Two Factor Authentication
        โดยพื้นฐาน ระบบจะมี passwod ของ user เป็น factor ที่ 1 ในการยืนยันตัวตนเข้าระบบ และมีการใช้รหัสอีกชุดที่เป็น factor ตัวที่ 2 เพื่อใช้ในการยืนยันตัวตนเข้าระบบเพิ่มขึ้นมาจากเดิม
        ex. SMS-OTP, Email-OTP, Google Authenticator เป็นต้น
    -   Capcha หรือ Re-capcha
        เป็นการทำโจทย์เล็กๆน้อยๆที่คอมพิวเตอร์ทั่วไปทำไม่ได้ เพื่อเป็นการกรอง submit ที่มาจากคนจริงๆ ช่วยให้ระบบสามารถป้องกัน brute force scripting หรือ automate brute force ได้
-   Back-end ควรใช้เครื่องมือเหล่านี้ในการป้องกัน
    -   Rate Limiting [IP, Account lockout]
        เมื่อมีการพยายามยิง request มาที่ ระบบ authentication ระบบจะทำการ track จำนวนครั้งที่ user หรือ IP นั้นๆ ใช้ creadentail login ผิดพลาด เพื่อเมื่อถึง threshold ที่ตั้งไว้ เช่น login ผิด 5 ครั้ง จะทำการ Block user หรือ IP นั้นๆถาวร/ชั่วคราวโดยมีระยะเวลาเท่าไหรตามแต่ policy ของบริษัท
    -   Logging & Monitoring
        ทำ log การ request login เพื่อตรวจจับ request ที่น่าสงสัย เช่น มีการพยายาม login หลาย request จาก IP เดิม เป็นต้น 
์Note : ในมุมมองของผม การทำระบบให้มีความปลอดภัยนั้นเป็นสิ่งที่ดี แต่ควรพึงระลึกไว้เสมอว่ามันเป็นการ trade of ระหว่างความปลอดภัยกับ user experience ดังนั้นเราควรวางระบบให้ balance กันทั้ง 2 ฝั่ง

> 2. จงเขียนตัวอย่าง sql query ในโค้ด php โดยให้มีชุดคำสั่งที่ช่วยป้องกัน sql injection (ตั้งชื่อตารางชื่อฟิลด์ด้วยตัวเองได้เลย)
```php
<?php
$condb = new mysqli("localhost","username","password","database");

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username = ? AND password = ?";
$statement = $condb->prepare($sql);

>
```

> 3. จงเขียน sql query ที่มี sub query ในตำแหน่งต่างๆ อย่างน้อยสองแบบ (ตั้งชื่อตารางชื่อฟิลด์ด้วยตัวเองได้เลย)

Create seed data

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

```sql
-- SQL statement --
SELECT E.Name, D.Name AS Department, S.Salary
FROM Employees E
JOIN Departments D ON E.DepartmentID = D.ID
JOIN Salaries S ON E.ID = S.EmployeeID
WHERE S.Salary = (
    SELECT MAX(Salary)
    FROM Salaries
    WHERE EmployeeID IN (
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

> 4. จากตาราง tb_product(id,name,status,shop_id) และ tb_shop(id,name) จงเขียน โค้ด select เพื่อแสดงสินค้าของร้าน ที่มีชื่อร้าน "rudy shop"

```

คำตอบบบ

```

> 5. เขียนคำสั่ง update สินค้าทุกตัวของร้าน "rudy shop" ให้มี status='0'

```

คำตอบบบ

```

> 6. จงเขียน function ของ code sql เพื่อปรับรูปแบบการ select ข้อมูล ตามประเภทข้อมูลดังนี้เพื่อให้ได้ผลลัพธืดังตัวอย่าง type date ให้แสดงผลเป็น dd/mm/YYYY type float,double ให้แสดงผลเป็น x,xxx,xxx.xx (สามารถเขียนได้มากกว่า 2 ข้อที่ยกตัวอย่าง)

```

คำตอบบบ

```

> 7. จงเขียน code function php ในการคำนวณผลลัพธ์ใบเสนอราคาโดยหัวข้อมีดังนี้
>    ราคาสินค้ารวม = สามารถตั้งเองได้
>    ส่วนลดรวม = สามารถตั้งเองได้
>    ราคาสินค้าหลังส่วนลด
>    ภาษีมูลค่าเพิ่ม 7 %
>    ราคารวมสุทธิ

```

คำตอบบบ

```

(ถ้าใช้ framework zend laravel node.js จะพิจารณาเป็นพิเศษ)

```

```
