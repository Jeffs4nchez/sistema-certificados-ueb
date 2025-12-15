#!/usr/bin/env python3
"""
Script para crear certificado de prueba en PostgreSQL
"""

import psycopg2
import sys

try:
    # Conectar a PostgreSQL
    print("Conectando a PostgreSQL...")
    
    conn = psycopg2.connect(
        host="localhost",
        port="5432",
        database="certificados_sistema",
        user="postgres",
        password="jeffo2003"
    )
    
    cursor = conn.cursor()
    print("✅ Conectado a la BD\n")
    
    # SQL para crear certificado
    sql_commands = [
        """
        INSERT INTO certificados 
        (numero_certificado, institucion, descripcion, fecha_elaboracion, monto_total, estado, usuario_creacion)
        VALUES 
        ('CERT-PRUEBA-001', 'Universidad Estatal de Bolívar', 'Certificado de prueba con 3 items', CURRENT_DATE, 1800.00, 'PENDIENTE', 'SISTEMA');
        """,
        """
        INSERT INTO detalle_certificados 
        (certificado_id, programa_id, item_id, organismo_id, naturaleza_id, descripcion_item, monto)
        SELECT c.id, 1, 1, 1, 1, 'Item 1 - Monto: $1000.00', 1000.00
        FROM certificados c WHERE c.numero_certificado = 'CERT-PRUEBA-001';
        """,
        """
        INSERT INTO detalle_certificados 
        (certificado_id, programa_id, item_id, organismo_id, naturaleza_id, descripcion_item, monto)
        SELECT c.id, 1, 1, 1, 1, 'Item 2 - Monto: $500.00', 500.00
        FROM certificados c WHERE c.numero_certificado = 'CERT-PRUEBA-001';
        """,
        """
        INSERT INTO detalle_certificados 
        (certificado_id, programa_id, item_id, organismo_id, naturaleza_id, descripcion_item, monto)
        SELECT c.id, 1, 1, 1, 1, 'Item 3 - Monto: $300.00', 300.00
        FROM certificados c WHERE c.numero_certificado = 'CERT-PRUEBA-001';
        """
    ]
    
    # Ejecutar comandos
    print("📋 Creando certificado con 3 items...\n")
    
    for i, sql in enumerate(sql_commands, 1):
        cursor.execute(sql)
        print(f"✅ Comando {i} ejecutado")
    
    # Commit
    conn.commit()
    print("\n✅ Cambios guardados en la BD\n")
    
    # Mostrar resultado
    print("=" * 50)
    print("  CERTIFICADO CREADO")
    print("=" * 50 + "\n")
    
    cursor.execute("""
        SELECT 
            c.id,
            c.numero_certificado,
            c.monto_total,
            COUNT(dc.id) as num_items
        FROM certificados c
        LEFT JOIN detalle_certificados dc ON dc.certificado_id = c.id
        WHERE c.numero_certificado = 'CERT-PRUEBA-001'
        GROUP BY c.id, c.numero_certificado, c.monto_total
    """)
    
    cert = cursor.fetchone()
    
    if cert:
        print(f"🆔 Número: {cert[1]}")
        print(f"💰 Monto Total: ${cert[2]:,.2f}")
        print(f"📦 Items: {cert[3]}\n")
        
        # Mostrar detalles
        print("📋 DETALLES DEL CERTIFICADO:\n")
        
        cursor.execute(f"""
            SELECT id, descripcion_item, monto FROM detalle_certificados 
            WHERE certificado_id = {cert[0]}
            ORDER BY id ASC
        """)
        
        detalles = cursor.fetchall()
        
        for idx, (item_id, desc, monto) in enumerate(detalles, 1):
            print(f"   Item {idx}:")
            print(f"      ID: {item_id}")
            print(f"      Descripción: {desc}")
            print(f"      Monto: ${monto:,.2f}\n")
    
    cursor.close()
    conn.close()
    
    print("=" * 50)
    print("✨ PRÓXIMOS PASOS")
    print("=" * 50 + "\n")
    print("Ahora puedes liquidar los items:\n")
    print("POST /api/liquidaciones")
    print('Body: {"detalle_certificado_id": ID, "cantidad_liquidacion": 300}\n')
    
except psycopg2.OperationalError as e:
    print(f"❌ ERROR DE CONEXIÓN: {e}")
    print("\nVerifica que:")
    print("  1. PostgreSQL esté ejecutándose")
    print("  2. Las credenciales sean correctas")
    print("  3. La BD 'certificados_sistema' exista")
    sys.exit(1)
except Exception as e:
    print(f"❌ ERROR: {e}")
    sys.exit(1)
