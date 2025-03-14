from fastapi import FastAPI, HTTPException, Request
import pymysql

app = FastAPI()

# Database connection settings
DB_HOST = "lion-warehouse-db-1"
DB_USER = "root"
DB_PASSWORD = "password"
DB_NAME = "warehouseDB"

# Connect to the database
def get_db_connection():
    return pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASSWORD, database=DB_NAME, cursorclass=pymysql.cursors.DictCursor)

@app.get("/warehouse/all")
def get_all_warehouse_items():
    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute("SELECT * FROM warehouse")
        items = cursor.fetchall()
    conn.close()
    return items

@app.post("/warehouse/insert")
async def insert_item(request: Request):
    data = await request.json()

    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "INSERT INTO warehouse (upc, qty, availability) VALUES (%s, %s, %s)"
        cursor.execute(sql, (data["upc"], data["qty"], data["availability"]))
        conn.commit()
    conn.close()

    return {"message": "Item inserted successfully", **data}

@app.get("/warehouse/qty/{upc}")
def get_qty(upc: str):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "SELECT qty FROM warehouse WHERE upc = %s"
        cursor.execute(sql, (upc,))
        result = cursor.fetchone()
    conn.close()

    if not result:
        raise HTTPException(status_code=404, detail="Item not found")

    return {"upc": upc, "quantity": result["qty"]}

@app.get("/warehouse/availability/{upc}")
def get_availability(upc: str):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "SELECT availability FROM warehouse WHERE upc = %s"
        cursor.execute(sql, (upc,))
        result = cursor.fetchone()
    conn.close()

    if not result:
        raise HTTPException(status_code=404, detail="Item not found")

    return {"upc": upc, "availability": result["availability"]}

@app.put("/warehouse/update/{upc}")
async def update_item(upc: str, request: Request):
    data = await request.json()

    update_fields = []
    values = []

    if "qty" in data:
        update_fields.append("qty = %s")
        values.append(data["qty"])
    if "availability" in data:
        update_fields.append("availability = %s")
        values.append(data["availability"])
    if "location" in data:
        update_fields.append("location = %s")
        values.append(data["location"])

    if not update_fields:
        raise HTTPException(status_code=400, detail="No fields provided for update")

    values.append(upc)
    query = f"UPDATE warehouse SET {', '.join(update_fields)} WHERE upc = %s"

    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute(query, tuple(values))
        conn.commit()
    conn.close()

    return {"message": "Item updated successfully", "upc": upc, **data}


@app.put("/warehouse/updateQty/{upc}")
async def update_qty(upc: str, request: Request):
    data = await request.json()
    
    if "qty" not in data:
        raise HTTPException(status_code=400, detail="Missing 'qty' field")

    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "UPDATE warehouse SET qty = %s WHERE upc = %s"
        cursor.execute(sql, (data["qty"], upc))
        conn.commit()
    conn.close()

    return {"message": "Quantity updated successfully", "upc": upc, "quantity": data["qty"]}

@app.put("/warehouse/updateAvailability/{upc}")
async def update_availability(upc: str, request: Request):
    data = await request.json()
    
    if "availability" not in data:
        raise HTTPException(status_code=400, detail="Missing 'availability' field")

    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "UPDATE warehouse SET availability = %s WHERE upc = %s"
        cursor.execute(sql, (data["availability"], upc))
        conn.commit()
    conn.close()

    return {"message": "Availability updated successfully", "upc": upc, "availability": data["availability"]}


@app.delete("/warehouse/delete/{upc}")
def delete_item(upc: str):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute("DELETE FROM warehouse WHERE upc = %s", (upc,))
        conn.commit()
    conn.close()

    return {"message": "Item deleted successfully", "upc": upc}


@app.get("/")
def read_root():
    return {"message": "Warehouse API is running!"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)

