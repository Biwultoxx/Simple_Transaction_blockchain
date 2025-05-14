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

        # Genesis block
        genesis_hash = self.hash_block("hash_block_pertama")
        self.append_block(
            hash_of_previous_block=genesis_hash,
            nonce=self.proof_of_work(0, genesis_hash, [])
        )

        # Smart contract: bonus transaksi besar
        def bonus_for_large_transaction(tx):
            bonus = 0
            amount = tx.get("amount", 0)
            if amount > 1000:
                bonus = 50
            elif amount > 500:
                bonus = 20
            elif amount > 100:
                bonus = 5

            if bonus > 0:
                return [{
                    "sender": "0",
                    "recipient": tx["sender"],  # Bonus ke pengirim
                    "amount": bonus
                }]
            return []

        self.contracts.append(bonus_for_large_transaction)

        # Load chain dari file jika ada
        self.load_chain()

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

        # Simpan ke file
        self.save_chain()

        return block

    def add_transaction(self, sender, recipient, amount):
        if amount <= 0:
            raise ValueError("Jumlah transaksi tidak valid (harus lebih dari 0)")

        # Jika reward dari sistem (sender == "0"), jangan hash
        sender_hashed = sender if sender == "0" else hashlib.sha256(sender.encode()).hexdigest()
        recipient_hashed = recipient if recipient == "0" else hashlib.sha256(recipient.encode()).hexdigest()

        transaction = {
            'sender': sender_hashed,
            'recipient': recipient_hashed,
            'amount': amount
        }

        self.current_transactions.append(transaction)

        # Smart contract
        for contract in self.contracts:
            try:
                additional = contract(transaction)
                if isinstance(additional, list):
                    self.current_transactions.extend(additional)
            except Exception as e:
                print(f"Smart contract error: {e}")

        # Simpan log transaksi
        with open("transaction_log/transactions.log", "a") as f:
            f.write(json.dumps(transaction) + "\n")

        return self.last_block['index'] + 1

    def save_chain(self):
        with open("transaction_chain/blockchain.json", "w") as f:
            json.dump(self.chain, f, indent=4)

    def load_chain(self):
        if os.path.exists("transaction_chain/blockchain.json"):
            with open("transaction_chain/blockchain.json", "r") as f:
                self.chain = json.load(f)

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

@app.route('/mining', methods=['GET'])
def mine_block():
    blockchain.add_transaction(
        sender='0',
        recipient=node_identifier,
        amount=1
    )

    last_block = blockchain.last_block
    last_block_hash = blockchain.hash_block(last_block)
    index = len(blockchain.chain)
    nonce = blockchain.proof_of_work(index, last_block_hash, blockchain.current_transactions)
    block = blockchain.append_block(nonce, last_block_hash)

    response = {
        'message': 'Block baru telah ditambang',
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
