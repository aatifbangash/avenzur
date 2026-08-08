#!/bin/bash
# ============================================================
# Cost Center SQL Migration Runner Script
# ============================================================
# This script runs all Cost Center SQL migrations in order
# Usage: bash run_migrations.sh
# ============================================================

set -e

# Color codes for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Database credentials
DB_HOST="localhost"
DB_USER="admin"
DB_PASS="R00tr00t"
DB_NAME="retaj_aldawa"

# Migration directory
MIGRATION_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     COST CENTER SQL MIGRATION RUNNER                ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""
echo "Database: $DB_NAME"
echo "Host: $DB_HOST"
echo "Directory: $MIGRATION_DIR"
echo ""

# Function to run SQL file
run_migration() {
    local file=$1
    local name=$2
    
    echo -e "${BLUE}Running: $name${NC}"
    
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$MIGRATION_DIR/$file" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ $name completed successfully${NC}"
        return 0
    else
        echo -e "${RED}✗ $name failed!${NC}"
        return 1
    fi
}

# Run migrations in order
echo -e "${BLUE}Starting migrations...${NC}"
echo ""

run_migration "001_create_dimensions.sql" "Migration 1: Create Dimensions"
run_migration "002_create_fact_table.sql" "Migration 2: Create Fact Table"
run_migration "003_create_etl_audit_log.sql" "Migration 3: Create ETL Audit Log"
run_migration "004_load_sample_data.sql" "Migration 4: Load Sample Data"

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║          ALL MIGRATIONS COMPLETED SUCCESSFULLY      ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════╝${NC}"
echo ""

# Verification
echo "Verifying installation..."
echo ""

PHARMACY_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -se "SELECT COUNT(*) FROM sma_dim_pharmacy;")
BRANCH_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -se "SELECT COUNT(*) FROM sma_dim_branch;")
FACT_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -se "SELECT COUNT(*) FROM sma_fact_cost_center;")
TOTAL_REVENUE=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -se "SELECT COALESCE(SUM(total_revenue), 0) FROM sma_fact_cost_center;")

echo "Pharmacies loaded: $PHARMACY_COUNT"
echo "Branches loaded: $BRANCH_COUNT"
echo "Fact records loaded: $FACT_COUNT"
echo "Total revenue: SAR $TOTAL_REVENUE"
echo ""
echo "✓ Installation verified!"
