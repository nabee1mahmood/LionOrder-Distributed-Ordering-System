from fastapi import FastAPI, HTTPException, Request
import pymysql

app = FastAPI()

# Database connection settings
DB_HOST = "lion-customers-db-1"
DB_USER = "root"
DB_PASSWORD = "password"
DB_NAME = "customersDB"

# Connect to the database
def get_db_connection():
    return pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASSWORD, database=DB_NAME, cursorclass=pymysql.cursors.DictCursor)

@app.put("/customers/update/{customer_id}")
async def update_customer(customer_id: int, request: Request):
    data = await request.json()
    
    conn = get_db_connection()
    with conn.cursor() as cursor:
        update_fields = []
        values = []
        
        if "fname" in data:
            update_fields.append("fname = %s")
            values.append(data["fname"])
        if "lname" in data:
            update_fields.append("lname = %s")
            values.append(data["lname"])
        if "email" in data:
            update_fields.append("email = %s")
            values.append(data["email"])
        if "user" in data:
            update_fields.append("user = %s")
            values.append(data["user"])
        if "pw" in data and data["pw"]:
            update_fields.append("pw = %s")
            values.append(data["pw"])

        if not update_fields:
            raise HTTPException(status_code=400, detail="No fields provided for update")

        values.append(customer_id)
        sql = f"UPDATE customers SET {', '.join(update_fields)} WHERE id = %s"
        cursor.execute(sql, tuple(values))
        conn.commit()

    conn.close()
    return {"message": "Customer updated successfully", "customer_id": customer_id, **data}


@app.post("/customers/authenticate")
def authenticate_customer(data: dict):
    email = data.get("email")
    password = data.get("password")

    conn = get_db_connection()
    with conn.cursor() as cursor:
        cursor.execute("SELECT id FROM customers WHERE email = %s AND pw = %s", (email, password))
        user = cursor.fetchone()
    conn.close()

    if user:
        return {"message": "Login successful", "user_id": user["id"]}
    else:
        raise HTTPException(status_code=401, detail="Invalid email or password")

# Create a new customer (JSON)
@app.post("/customers/")
async def create_customer(request: Request):
    data = await request.json()  # Get JSON data 


    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "INSERT INTO customers (fname, lname, email, user, pw) VALUES (%s, %s, %s, %s, %s)"
        cursor.execute(sql, (data["fname"], data["lname"], data["email"], data["user"], data["pw"]))
        conn.commit()
        customer_id = cursor.lastrowid
    conn.close()
    
    return {"id": customer_id, **data}

# Get customer by ID (returns JSON)
@app.get("/customers/{customer_id}")
def get_customer(customer_id: int):
    conn = get_db_connection()
    with conn.cursor() as cursor:
        sql = "SELECT id, fname, lname, email, user FROM customers WHERE id = %s"
        cursor.execute(sql, (customer_id,))
        customer = cursor.fetchone()
    conn.close()

    if not customer:
        raise HTTPException(status_code=404, detail="Customer not found")

    return customer

if __name__ == "__main__":
    import os
    import socketserver
    import http.server

    # Run FastAPI using Python's built-in HTTP server
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
