
# Database Replication with Laravel and Docker

This guide explains how to set up database replication in a Laravel application using Docker. Database replication involves creating copies of a database and storing them across multiple on-premises or cloud destinations to improve data availability and accessibility.

---

## Table of Contents
- [Database Replication Overview](#database-replication-overview)
- [Setting Up Database Replication in Laravel](#setting-up-database-replication-in-laravel)
    - [Step 1: Configure Multiple MySQL Servers](#step-1-configure-multiple-mysql-servers)
    - [Step 2: Database Configuration in Laravel](#step-2-database-configuration-in-laravel)
    - [Sticky Configuration](#sticky-configuration)
    - [Environment File Configuration](#environment-file-configuration)
- [Database Replication Process](#database-replication-process)
    - [Master Configuration](#master-configuration)
    - [Slave Configuration](#slave-configuration)

---

## Database Replication Overview

Database replication is the process of creating copies of a database and storing them across various on-premises or cloud destinations. It improves data availability and accessibility by allowing every user connected to the system to access copies of the same up-to-date data.

---

## Setting Up Database Replication in Laravel

### Step 1: Configure Multiple MySQL Servers

Ensure that you have multiple MySQL servers running, one primary (write) and multiple replicas (read).

### Step 2: Database Configuration in Laravel

Update your `database.php` file as follows:

```php
'mysql' => [
    'read' => [
        'host' => [
            'mysql-replica-1',
            'mysql-replica-2',
            'mysql-replica-3',
        ],
    ],
    'write' => [
        'host' => 'mysql-primary',
    ],
    'driver' => 'mysql',
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'sticky' => true,
    'prefix' => '',
],
```

### Sticky Configuration

One potential issue with replication is that after a write operation, the read replica may not reflect the updated record immediately. Enabling **"sticky"** ensures that any read operation following a write will use the write connection to retrieve the latest data.

### Environment File Configuration

Update your `.env` file to include both primary and replica database hosts:

```env
# Primary Database (Write)
DB_CONNECTION=mysql
DB_HOST=mysql-primary
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=password

# Replica Databases (Read)
DB_READ_HOST_1=mysql-replica-1
DB_READ_HOST_2=mysql-replica-2
DB_READ_HOST_3=mysql-replica-3
```

---

## Database Replication Process

To enable data replication, specific configurations are required on the databases:

### Master Configuration

On the **master container**, set the following parameters in the MySQL configuration file:

```ini
[mysqld]
server-id=1
log-bin=mysql-bin
binlog-do-db=laravel
```

Connect to the MySql shell of container and create a replication user:

```sql
CREATE USER 'replicator'@'%' IDENTIFIED BY 'replica_password';
GRANT REPLICATION SLAVE ON *.* TO 'replicator'@'%';
FLUSH PRIVILEGES;
```

Check the master status to note the binary log file and position:

```sql
SHOW MASTER STATUS;
```

The output will look like this:

| File            | Position | Binlog_Do_DB | Binlog_Ignore_DB |
|-----------------|----------|--------------|-------------------|
| mysql-bin.000001 | 123      | laravel      |                   |

Note the values of file and position

### Slave Configuration

On each **slave container**, set a unique `server-id` and specify the database to replicate:

```ini
[mysqld]
server-id=2
replicate-do-db=laravel
```

Connect to the slave's MySQL shell and run:

```sql
CHANGE MASTER TO 
    MASTER_HOST='mysql-primary',
    MASTER_USER='replicator',
    MASTER_PASSWORD='replica_password',
    MASTER_LOG_FILE='mysql-bin.000001',
    MASTER_LOG_POS=123;

START SLAVE;
SHOW SLAVE STATUS\G
```

Confirm that `Slave_IO_Running` and `Slave_SQL_Running` are both `Yes`.

Repeat this process for all of slave nodes after that you'll be good to go.

---

