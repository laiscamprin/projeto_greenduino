#include <Wire.h>

#include <AHTxx.h> //Inclui a biblioteca AHT21

#include <LiquidCrystal_I2C.h>
LiquidCrystal_I2C lcd(0x27,16,2);  // set the LCD address to 0x27 for a 16 chars and 2 line display

#define ChaveBomba 3
#define Bomba 2

float ahtValue;                               //to store T/RH result

AHTxx aht20(AHTXX_ADDRESS_X38, AHT2x_SENSOR); //sensor address, sensor type

long tempo=0,tempo1=0,tempoleitura=60000*5,tempoenviapy=60000*25;

int leitura_sensor = 0;
const int VALOR_MAXIMO = 1023; //Valor com solo seco
const int VALOR_MINIMO = 0; //Valor com solo umido

float  tempDeg;
float hum;

float umidade_minima = 50.0;
int leitura_sensor_minima = 20;

bool erroaht = false,decide = false, debug = false, entrouirrigacao = false;
bool irrigou = false;
void setup() 
{
  Serial.begin(9600);

  pinMode(ChaveBomba,INPUT_PULLUP);
  pinMode(Bomba,OUTPUT);
  //digitalWrite(Bomba,LOW);
  //delay(2500);
  digitalWrite(Bomba,HIGH);

  Serial.begin(9600);   
  delay(1000);
  lcd.init(); 
  lcd.backlight(); 
  lcd.clear(); 
  
  while (aht20.begin() != true)
  {    
    erroaht = true;
    lcd.clear();
    lcd.setCursor(1,0); 
    lcd.print("Err In AHT!"); 
    if (debug) Serial.println("Erro aht!");
    delay(2000);
  }

  if (debug) Serial.println(F("AHT20 OK"));

  lcd.clear();
  lcd.setCursor(1,0); 
  lcd.print("GreenDuino"); 
  lcd.setCursor(1,1); 
  lcd.print("V1.2 20/10/2025"); 
  delay(2000);
  lcd.clear();

}

void loop() 
{  

  if (!erroaht) LeAHT21();

   LeUmidadeSoloNovo();

  if ((millis() - tempo) >= tempoleitura)
  {  
    tempo = millis();
    decide = true;    
  }

  if (digitalRead(ChaveBomba)==LOW)
  {
    Irrigando();
  }

  if (((millis() - tempo1) >= tempoenviapy) || entrouirrigacao )
  { 
    tempo1 = millis();
    EnviaPython();
  }
}

void EnviaPython()
{
  String sumidadesolo  = String(leitura_sensor); 
  String sumidadear  =  String(hum, 2);    
  String stempar = String(tempDeg,2);
  String sirrigacao = String(entrouirrigacao ? 1 : 0);    //String(irrigou ? 1 : 0);
  
  String mensagem = sumidadesolo + ";" + sumidadear + ";" + stempar + ";" + sirrigacao + ";";
  Serial.println(mensagem);

  entrouirrigacao = false;
}

void LeUmidadeSoloNovo()
{
    leitura_sensor = analogRead(A0);
    if (debug) Serial.println(leitura_sensor);
    leitura_sensor = map(leitura_sensor, VALOR_MINIMO, VALOR_MAXIMO, 100, 0);
    if (debug) 
    {
      Serial.print("Umidade Solo: "); 
      Serial.println(leitura_sensor);
    } 
    
    if ((leitura_sensor <= leitura_sensor_minima) && (hum <= umidade_minima ) && decide)
    { 
    
      if (debug) Serial.println("Status: Solo seco");
      lcd.clear();
      lcd.setCursor(1,0); 
      lcd.print("Solo Seco!");             
      delay(1000);
      Irrigando();
      decide = false;
    }
    else
    {
      lcd.clear();
      lcd.setCursor(1,0); 
      lcd.print("Um.Solo:"); 
      lcd.setCursor(9,0); 
      lcd.print(leitura_sensor);
      lcd.setCursor(15,0); 
      lcd.print("%");
      if (debug) Serial.println(" ");
    }
    delay(2000);     
}

void LeAHT21(void)
{   
    bool lcont = true; 

    
    tempDeg =aht20.readTemperature(); // Define a variavel TemperaturaAHT com o valor da leitura da umidade feita pelo sensor AHT21
    if (tempDeg == AHTXX_ERROR)
    {
      lcont = false;
      if (debug) Serial.println(AHTXX_ERROR);
    }
    //delay(1000) ;
    hum = aht20.readHumidity(); // Define a variavel UmidadeAHT com o valor da leitura da umidade feita pelo sensor AHT21
    if (hum == AHTXX_ERROR)
    {
      lcont = false;
      if (debug) Serial.println(AHTXX_ERROR);
    }
    if ( lcont) 
    {
      if (debug)
      {
        Serial.println("AR...");
        Serial.print("Temperatura ");  Serial.println(tempDeg);
        Serial.print("Umidade ");  Serial.println(hum);
        Serial.println(" ");
      }
      lcd.clear();
      lcd.setCursor(1,0); 
      lcd.print("Umi.Ar:"); 
      lcd.setCursor(8,0); 
      lcd.print(hum);
      lcd.setCursor(15,0); 
      lcd.print("%");

      lcd.setCursor(1,1); 
      lcd.print("Temp.Ar:"); 
      lcd.setCursor(8,1); 
      lcd.print(tempDeg);
      lcd.setCursor(15,1); 
      lcd.print("C");
    }
    else
    {
      lcd.clear();
      lcd.setCursor(1,0); 
      lcd.print("Err:T/H..."); 
    } 
    delay(2000);  
}

void Irrigando()
{
  long tb = millis();
  long tbf = 5000;
  long tb1 = millis();

  irrigou = true;

  digitalWrite(Bomba,LOW);
  lcd.clear();
  lcd.setCursor(1,0); 
  lcd.print("Irrigando..."); 

  while ((millis()-tb)<tbf)
  {
   if (irrigou)
    {
      entrouirrigacao = true;
      if (debug) Serial.println("Irrigando...");
      irrigou = false;
    }
  }
  digitalWrite(Bomba,HIGH);

}
