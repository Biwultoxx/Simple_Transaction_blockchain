### Tugas Digital Asset Semester 4
Nama : 
- Bima Aditya A.G
- Priska Antonia W.O
- Elisa Siti Va'iya

# 💸 Aplikasi Pengiriman Uang Berbasis Blockchain Sederhana

Aplikasi ini adalah sistem web sederhana untuk melakukan transfer uang antar pengguna, dilengkapi dengan bonus dan pencatatan transaksi ke blockchain lokal. Program ini bertujuan untuk pembelajaran logika blockchain secara sederhana

## 🛠️ Fitur
- Register & Login
- Transfer uang antar pengguna
- Riwayat transaksi (opsional untuk dikembangkan)
- Antarmuka HTML/CSS yang bersih
## Smart Contract Sederhana Dengan Function Python dan PHP
- Bonus otomatis berdasarkan nominal transfer
- Blockchain endpoint untuk mencatat transaksi

---

## 📦 Teknologi Digunakan

### Backend Web (XAMPP)
- PHP 7+
- MySQL (untuk user & saldo)
- Session-based Authentication

### Blockchain API (Python)
- Flask
- Requests

---

## ⚙️ Instalasi

### 1. Clone Repositori

```bash
git clone https://github.com/Biwultoxx/Simple_Transaction_blockchain.git
cd Simple_Transaction_blockchain

```
### 2. Install Requirements

```bash 
pip install -r requirements.txt
```

### 3. Konfigurasi DB 

```bash 
CREATE DATABASE transfer_app;

USE transfer_app;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    balance DOUBLE DEFAULT 0
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender VARCHAR(50),
    recipient VARCHAR(50),
    amount DOUBLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 4. Jalankan Backend Python

```bash 
python blockchain.py
```
### 5. Buka pada http://localhost:80
