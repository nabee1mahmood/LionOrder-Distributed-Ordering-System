from fastapi import FastAPI, HTTPException, Request
import pymysql
import json
from datetime import datetime

app = FastAPI()

DB_HOST = "lion-orders-db-1"  
DB_USER = "root"
DB_PASSWORD = "password"
DB_NAME = "ordersDB"

# Connect to the database
def get_db_connection():
    return pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASSWORD, database=DB_NAME, cursorclass=pymysql.cursors.DictCursor)


@app.post("/orders/insert")
async def insert_order(request: Request):
    data = await request.json()  # Get JSON data
    order_date = datetime.now().strftime('%Y-%m-%d %H:%M:%S')  
    order_contents = json.dumps(data["orderContents"])  

    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "INSERT INTO orders (custID, orderStatus, orderDate, orderContents) VALUES (%s, %s, %s, %s)"
        cursor.execute(sql, (data["custID"], data["orderStatus"], order_date, order_contents))
        conn.commit()
        order_id = cursor.lastrowid
    conn.close()

    return {"orderID": order_id, "custID": data["custID"], "orderStatus": data["orderStatus"], "orderContents": data["orderContents"]}

@app.get("/orders/get/{orderID}")
def get_order(orderID: int):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "SELECT orderID, custID, orderStatus, orderDate, orderContents FROM orders WHERE orderID = %s"
        cursor.execute(sql, (orderID,))
        order = cursor.fetchone()
    conn.close()

    if not order:
        raise HTTPException(status_code=404, detail="Order not found")

    # Convert orderContents from JSON string back to a list
    order["orderContents"] = json.loads(order["orderContents"])
    return order


@app.get("/orders/status/{orderStatus}")
def get_orders_by_status(orderStatus: str):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "SELECT orderID, custID, orderStatus, orderDate, orderContents FROM orders WHERE orderStatus = %s"
        cursor.execute(sql, (orderStatus,))
        orders = cursor.fetchall()
    conn.close()

    # Convert orderContents for all orders
    for order in orders:
        order["orderContents"] = json.loads(order["orderContents"])

    return orders

@app.get("/orders/customer/{custID}")
def get_orders_by_custID(custID: int):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "SELECT orderID, custID, orderStatus, orderDate, orderContents FROM orders WHERE custID = %s"
        cursor.execute(sql, (custID,))
        orders = cursor.fetchall()
    conn.close()

    if not orders:
        raise HTTPException(status_code=404, detail="No orders found for this customer")

    # Convert orderContents from JSON string back to a list
    for order in orders:
        order["orderContents"] = json.loads(order["orderContents"])

    return orders

@app.put("/orders/update/{orderID}")
async def update_order(orderID: int, request: Request):
    data = await request.json() 
    update_fields = []
    values = []

    if "orderStatus" in data:
        update_fields.append("orderStatus = %s")
        values.append(data["orderStatus"])
    if "orderContents" in data:
        update_fields.append("orderContents = %s")
        values.append(json.dumps(data["orderContents"]))  # Store list as JSON string

    if not update_fields:
        raise HTTPException(status_code=400, detail="No fields provided for update")

    values.append(orderID)
    query = f"UPDATE orders SET {', '.join(update_fields)} WHERE orderID = %s"

    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute(query, tuple(values))
        conn.commit()
    conn.close()

    return {"message": "Order updated successfully", "orderID": orderID, **data}

@app.delete("/orders/delete/{orderID}")
def delete_order(orderID: int):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute("DELETE FROM orders WHERE orderID = %s", (orderID,))
        conn.commit()
    conn.close()

    return {"message": "Order deleted successfully", "orderID": orderID}

@app.get("/")
def read_root():
    return {"message": "Orders API is running!"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)

