import serial
import mysql.connector
from datetime import datetime, date

PORTA_SERIAL = 'COM3'  # Altere conforme seu sistema
BAUD_RATE = 9600

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '1234',
    'database': 'greenduino_db'
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
            data_obj = date.today()
            hora_obj = datetime.now().time()
            hora_str = hora_obj.strftime("%H:%M:%S")
            temperatura = partes[0]
            umidade_ar = partes[1]
            umidade_solo = partes[2]
            irrigacao = partes[3].strip().lower()
            if irrigacao not in ['true', 'false']:
                irrigacao = None  # ou 'false', dependendo do que faz sentido
            cursor.execute(
                "INSERT INTO registros (data, hora, temperatura, umidade_ar, umidade_solo, irrigacao) VALUES (%s, %s, %s, %s, %s, %s)",
                (data_obj, hora_str, temperatura, umidade_ar, umidade_solo, irrigacao)
            )
            conexao.commit()
            print("Registro inserido:", linha)
except KeyboardInterrupt:
    print("Encerrando...")
finally:
    cursor.close()
    conexao.close()
    arduino.close()
