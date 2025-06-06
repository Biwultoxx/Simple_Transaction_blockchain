import sys
import hashlib
import json
import os
from time import time
from uuid import uuid4
from flask import Flask, request, jsonify

class Blockchain:
    difficulty_target = "0000"

    def __init__(self):
        self.nodes = set()
        self.chain = []
        self.current_transactions = []
        self.contracts = []

        # Buat folder untuk penyimpanan jika belum ada
        os.makedirs("transaction_chain", exist_ok=True)
        os.makedirs("transaction_log", exist_ok=True)

        # Load chain dari file jika ada
        self.load_chain()

        # Jika chain kosong, buat genesis block
        if not self.chain:
            genesis_hash = self.hash_block("hash_block_pertama")
            self.append_block(
                hash_of_previous_block=genesis_hash,
                nonce=self.proof_of_work(0, genesis_hash, [])
            )

        # Smart contract: bonus transaksi besar
        def bonus_for_large_transaction(tx):
            bonus = 0
            amount = tx.get("amount", 0)
            if amount > 9999:
                bonus = 1000
            elif amount > 4999:
                bonus = 500
            elif amount > 999:
                bonus = 100

            if bonus > 0:
                return [{
                    "sender": "0",
                    "recipient": tx["sender"],
                    "amount": bonus
                }]
            return []

        self.contracts.append(bonus_for_large_transaction)

    def hash_block(self, block):
        if isinstance(block, str):
            block = {"data": block}
        block_encoded = json.dumps(block, sort_keys=True).encode()
        return hashlib.sha256(block_encoded).hexdigest()

    def proof_of_work(self, index, hash_of_previous_block, transactions):
        nonce = 0
        while not self.valid_proof(index, hash_of_previous_block, transactions, nonce):
            nonce += 1
        return nonce

    def valid_proof(self, index, hash_of_previous_block, transactions, nonce):
        content = f'{index}{hash_of_previous_block}{transactions}{nonce}'.encode()
        content_hash = hashlib.sha256(content).hexdigest()
        return content_hash[:len(self.difficulty_target)] == self.difficulty_target

    def append_block(self, nonce, hash_of_previous_block):
        block = {
            'index': len(self.chain),
            'timestamp': time(),
            'transaction': self.current_transactions,
            'nonce': nonce,
            'hash_of_previous_block': hash_of_previous_block
        }

        self.current_transactions = []
        self.chain.append(block)

        self.save_chain()
        return block

    def add_transaction(self, sender, recipient, amount):
        if amount <= 0:
            raise ValueError("Jumlah transaksi tidak valid (harus lebih dari 0)")

        sender_hashed = sender if sender == "0" else hashlib.sha256(sender.encode()).hexdigest()
        recipient_hashed = recipient if recipient == "0" else hashlib.sha256(recipient.encode()).hexdigest()

        transaction = {
            'sender': sender_hashed,
            'recipient': recipient_hashed,
            'amount': amount
        }

        self.current_transactions.append(transaction)

        # Jalankan smart contracts
        for contract in self.contracts:
            try:
                additional = contract(transaction)
                if isinstance(additional, list):
                    self.current_transactions.extend(additional)
            except Exception as e:
                print(f"Smart contract error: {e}")

        # Pastikan folder log ada dan simpan transaksi
        os.makedirs("transaction_log", exist_ok=True)
        with open("transaction_log/transactions.log", "a") as f:
            f.write(json.dumps(transaction) + "\n")

        return self.last_block['index'] + 1

    def save_chain(self):
        os.makedirs("transaction_chain", exist_ok=True)
        with open("transaction_chain/blockchain.json", "w") as f:
            json.dump(self.chain, f, indent=4)

    def load_chain(self):
        try:
            with open("transaction_chain/blockchain.json", "r") as f:
                self.chain = json.load(f)
        except FileNotFoundError:
            self.chain = []

    @property
    def last_block(self):
        return self.chain[-1]


# ---------------- Flask Server ----------------

app = Flask(__name__)
node_identifier = str(uuid4()).replace('-', '')
blockchain = Blockchain()

@app.route('/blockchain', methods=['GET'])
def full_chain():
    response = {
        'chain': blockchain.chain,
        'length': len(blockchain.chain)
    }
    return jsonify(response), 200

@app.route('/mining', methods=['POST'])
def mine_block():
    data = request.get_json()
    username = data.get('username')  # Ambil username dari PHP

    if not username:
        return jsonify({'error': 'Username is required'}), 400

    blockchain.add_transaction(
        sender='0',
        recipient=username,
        amount=500
    )

    last_block = blockchain.last_block
    last_block_hash = blockchain.hash_block(last_block)
    index = len(blockchain.chain)
    nonce = blockchain.proof_of_work(index, last_block_hash, blockchain.current_transactions)
    block = blockchain.append_block(nonce, last_block_hash)

    response = {
        'message': f'Block baru telah ditambang oleh {username}',
        'index': block['index'],
        'hash_of_previous_block': block['hash_of_previous_block'],
        'nonce': block['nonce'],
        'transaction': block['transaction']
    }
    return jsonify(response), 200

@app.route('/transaction/new', methods=['POST'])
def new_transaction():
    values = request.get_json()
    required = ['sender', 'recipient', 'amount']
    if not all(k in values for k in required):
        return jsonify({'error': 'Missing fields'}), 400

    try:
        index = blockchain.add_transaction(
            sender=values['sender'],
            recipient=values['recipient'],
            amount=float(values['amount'])
        )
        return jsonify({'message': f'Transaksi akan ditambahkan ke blok {index}'}), 201
    except ValueError as ve:
        return jsonify({'error': str(ve)}), 400

if __name__ == '__main__':
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 5000
    app.run(host='0.0.0.0', port=port)
