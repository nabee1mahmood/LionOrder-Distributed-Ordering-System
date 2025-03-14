from fastapi import FastAPI, HTTPException, Request
import pymysql

app = FastAPI()

DB_HOST = "lion-inventory-db-1"  
DB_USER = "root"
DB_PASSWORD = "password"
DB_NAME = "inventoryDB"  

# Function to connect to database
def get_db_connection():
     return pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASSWORD, database=DB_NAME, cursorclass=pymysql.cursors.DictCursor)

@app.get("/inventory/getByUPC/{upc}")
def get_item_by_upc(upc: str):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute("SELECT * FROM inventory WHERE UPC = %s", (upc,))
        item = cursor.fetchone()
    conn.close()

    if not item:
        raise HTTPException(status_code=404, detail="Item not found")

    return item



@app.get("/inventory/all")
def get_all_inventory():
    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute("SELECT * FROM inventory")
        items = cursor.fetchall()
    conn.close()
    return items

@app.get("/inventory/check/{itemID}")
def check_inventory_availability(itemID: int):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute("SELECT quantity FROM inventory WHERE itemID = %s", (itemID,))
        item = cursor.fetchone()
    conn.close()

    if not item:
        raise HTTPException(status_code=404, detail="Item not found")

    return {"itemID": itemID, "available_quantity": item["quantity"]}

@app.post("/inventory/insert")
async def insert_item(request: Request):
    data = await request.json()

    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "INSERT INTO inventory (itemName, UPC, quantity, price) VALUES (%s, %s, %s, %s)"
        cursor.execute(sql, (data["itemName"], data["UPC"], data["quantity"], data["price"]))
        conn.commit()
        itemID = cursor.lastrowid
    conn.close()

    return {"itemID": itemID, **data}

@app.get("/inventory/get/{itemID}")
def get_item(itemID: int):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute("SELECT * FROM inventory WHERE itemID = %s", (itemID,))
        item = cursor.fetchone()
    conn.close()

    if not item:
        raise HTTPException(status_code=404, detail="Item not found")

    return item

@app.put("/inventory/update/{itemID}")
async def update_item(itemID: int, request: Request):
    data = await request.json()
    update_fields = []
    values = []

    if "itemName" in data:
        update_fields.append("itemName = %s")
        values.append(data["itemName"])
    if "UPC" in data:
        update_fields.append("UPC = %s")
        values.append(data["UPC"])
    if "quantity" in data:
        update_fields.append("quantity = %s")
        values.append(data["quantity"])
    if "price" in data:
        update_fields.append("price = %s")
        values.append(data["price"])

    if not update_fields:
        raise HTTPException(status_code=400, detail="No fields provided for update")

    values.append(itemID)
    query = f"UPDATE inventory SET {', '.join(update_fields)} WHERE itemID = %s"

    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute(query, tuple(values))
        conn.commit()
    conn.close()

    return {"message": "Item updated successfully", "itemID": itemID, **data}

@app.delete("/inventory/delete/{itemID}")
def delete_item(itemID: int):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute("DELETE FROM inventory WHERE itemID = %s", (itemID,))
        conn.commit()
    conn.close()

    return {"message": "Item deleted successfully", "itemID": itemID}


@app.get("/")
def read_root():
    return {"message": "Inventory API is running!"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)  

