import serial
import mysql.connector
from datetime import datetime

PORTA_SERIAL = 'COM3'  # Altere conforme seu sistema
BAUD_RATE = 9600 

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '****',
    'database': 'exemplo_serial'
}

# Conecta ao banco
conexao = mysql.connector.connect(**DB_CONFIG)
cursor = conexao.cursor()

# Conecta à serial
arduino = serial.Serial(PORTA_SERIAL, BAUD_RATE, timeout=2)

try:
    while True:
        linha = arduino.readline().decode('utf-8').strip()
        if linha:
            partes = linha.split(';')
            data_obj = datetime.strptime(partes[0], "%Y-%m-%d").date()
            hora_obj = datetime.strptime(partes[1], "%H:%M:%S").time()
            info = partes[2]

            cursor.execute("INSERT INTO registros (data, hora, informacao) VALUES (%s, %s, %s)",
                           (data_obj, hora_obj, info))
            conexao.commit()
            print("Registro inserido:", linha)
except KeyboardInterrupt:
    print("Encerrando...")
finally:
    cursor.close()
    conexao.close()
    arduino.close()
    
