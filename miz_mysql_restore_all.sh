#!/bin/bash

# Prompt for MySQL username
read -p "Enter MySQL username: " username

# Prompt for MySQL password (hidden input)
read -s -p "Enter MySQL password: " password
echo ""  # Move to a new line after password input

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo "Error: MySQL client is not installed."
    exit 1
fi

# Find all .sql files in the current directory
sql_files=(*.sql)

if [ ${#sql_files[@]} -eq 0 ]; then
    echo "No .sql files found in the current directory."
    exit 1
fi

echo "Found ${#sql_files[@]} SQL files. Starting restore process..."

for file in "${sql_files[@]}"; do
    # Extract database name from filename (remove .sql extension)
    db_name="${file%.sql}"

    echo "Dropping database: $db_name (if exists)..."
    mysql -u"$username" -p"$password" -e "DROP DATABASE IF EXISTS \`$db_name\`;"

    echo "Creating database: $db_name..."
    mysql -u"$username" -p"$password" -e "CREATE DATABASE \`$db_name\`;"

    echo "Restoring $file into $db_name..."
    mysql -u"$username" -p"$password" "$db_name" < "$file"

    if [ $? -eq 0 ]; then
        echo "Successfully restored $file into $db_name"
    else
        echo "Error restoring $file"
    fi

    echo "--------------------------------------"
done

echo "All SQL files have been processed."

