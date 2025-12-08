<?php
/**
 * Database Schema Comparator
 * Compares two database schemas and generates migration scripts
 */

class SchemaComparator {
    
    /**
     * Parse schema text and extract tables with columns
     */
    private function parseSchema($schemaText) {
        $tables = [];
        $lines = explode("\n", $schemaText);
        $currentTable = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Match CREATE TABLE statements
            if (preg_match('/CREATE TABLE\s+`?(\w+)`?\s*\(/i', $line, $matches)) {
                $currentTable = $matches[1];
                $tables[$currentTable] = [];
                continue;
            }
            
            // Match column definitions
            if ($currentTable && !empty($line) && 
                !preg_match('/^(--|\/\*)/', $line)) {
                
                if (preg_match('/^`?(\w+)`?\s+([^,\s]+)/i', $line, $matches) &&
                    !preg_match('/^(PRIMARY|FOREIGN|KEY|UNIQUE|INDEX|CONSTRAINT)/i', $line)) {
                    
                    $columnName = $matches[1];
                    $columnDef = explode(',', $line)[0];
                    $columnDef = trim($columnDef);
                    
                    $tables[$currentTable][] = [
                        'name' => $columnName,
                        'definition' => $columnDef
                    ];
                }
            }
            
            // Reset current table on closing parenthesis
            if (preg_match('/^\);?$/', $line)) {
                $currentTable = null;
            }
        }
        
        return $tables;
    }
    
    /**
     * Compare two schemas and find differences
     */
    public function compareSchemas($schema1, $schema2) {
        $tables1 = $this->parseSchema($schema1);
        $tables2 = $this->parseSchema($schema2);
        
        $differences = [];
        $migrationStatements = [];
        
        // Find tables and columns in schema2 that are missing in schema1
        foreach ($tables2 as $tableName => $columns) {
            if (!isset($tables1[$tableName])) {
                // Entire table is missing
                $differences[] = [
                    'table' => $tableName,
                    'status' => 'table_missing',
                    'columns' => $columns
                ];
                
                // Generate CREATE TABLE statement
                $columnDefs = [];
                foreach ($columns as $col) {
                    $columnDefs[] = "  " . $col['definition'];
                }
                
                $migrationStatements[] = sprintf(
                    "-- Create missing table: %s\nCREATE TABLE %s (\n%s\n);\n",
                    $tableName,
                    $tableName,
                    implode(",\n", $columnDefs)
                );
            } else {
                // Table exists, check for missing columns
                $missingColumns = [];
                $schema1Columns = array_map(function($col) {
                    return strtolower($col['name']);
                }, $tables1[$tableName]);
                
                foreach ($columns as $col) {
                    if (!in_array(strtolower($col['name']), $schema1Columns)) {
                        $missingColumns[] = $col;
                    }
                }
                
                if (count($missingColumns) > 0) {
                    $differences[] = [
                        'table' => $tableName,
                        'status' => 'columns_missing',
                        'columns' => $missingColumns
                    ];
                    
                    // Generate ALTER TABLE statements
                    foreach ($missingColumns as $col) {
                        $migrationStatements[] = sprintf(
                            "-- Add missing column to %s\nALTER TABLE %s ADD COLUMN %s;\n",
                            $tableName,
                            $tableName,
                            $col['definition']
                        );
                    }
                }
            }
        }
        
        return [
            'differences' => $differences,
            'migration' => implode("\n", $migrationStatements)
        ];
    }
    
    /**
     * Generate HTML report
     */
    public function generateHTMLReport($differences, $migration) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Schema Comparison Report</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f5f5f5;
                }
                h1 { color: #2c3e50; }
                h2 { color: #34495e; margin-top: 30px; }
                .table-diff {
                    background: white;
                    padding: 15px;
                    margin: 15px 0;
                    border-radius: 5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .table-name {
                    font-size: 18px;
                    font-weight: bold;
                    color: #2980b9;
                }
                .status {
                    display: inline-block;
                    padding: 5px 10px;
                    border-radius: 3px;
                    font-size: 12px;
                    margin-left: 10px;
                }
                .status-missing { background: #e74c3c; color: white; }
                .status-partial { background: #f39c12; color: white; }
                .column-list {
                    margin: 10px 0 0 20px;
                }
                .column-item {
                    font-family: 'Courier New', monospace;
                    padding: 5px;
                    background: #ecf0f1;
                    margin: 5px 0;
                    border-left: 3px solid #3498db;
                    padding-left: 10px;
                }
                .migration-script {
                    background: #2c3e50;
                    color: #2ecc71;
                    padding: 20px;
                    border-radius: 5px;
                    font-family: 'Courier New', monospace;
                    white-space: pre-wrap;
                    overflow-x: auto;
                }
                .success {
                    background: #2ecc71;
                    color: white;
                    padding: 15px;
                    border-radius: 5px;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <h1>📊 Database Schema Comparison Report</h1>
            
            <h2>Differences Found</h2>
            <?php if (empty($differences)): ?>
                <div class="success">
                    ✓ Schemas are identical - no missing columns!
                </div>
            <?php else: ?>
                <?php foreach ($differences as $diff): ?>
                    <div class="table-diff">
                        <div class="table-name">
                            Table: <?php echo htmlspecialchars($diff['table']); ?>
                            <?php if ($diff['status'] === 'table_missing'): ?>
                                <span class="status status-missing">Entire table missing</span>
                            <?php else: ?>
                                <span class="status status-partial">
                                    <?php echo count($diff['columns']); ?> column(s) missing
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="column-list">
                            <strong>Missing columns:</strong>
                            <?php foreach ($diff['columns'] as $col): ?>
                                <div class="column-item">
                                    <?php echo htmlspecialchars($col['definition']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($migration)): ?>
                <h2>Migration Script</h2>
                <div class="migration-script"><?php echo htmlspecialchars($migration); ?></div>
            <?php endif; ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Generate plain text report
     */
    public function generateTextReport($differences, $migration) {
        $report = "DATABASE SCHEMA COMPARISON REPORT\n";
        $report .= str_repeat("=", 50) . "\n\n";
        
        $report .= "DIFFERENCES FOUND:\n";
        $report .= str_repeat("-", 50) . "\n";
        
        if (empty($differences)) {
            $report .= "✓ Schemas are identical - no missing columns!\n";
        } else {
            foreach ($differences as $diff) {
                $report .= "\nTable: " . $diff['table'] . "\n";
                $report .= "Status: " . ($diff['status'] === 'table_missing' ? 
                    'ENTIRE TABLE MISSING' : 
                    count($diff['columns']) . ' COLUMN(S) MISSING') . "\n";
                $report .= "Missing columns:\n";
                
                foreach ($diff['columns'] as $col) {
                    $report .= "  - " . $col['definition'] . "\n";
                }
            }
        }
        
        if (!empty($migration)) {
            $report .= "\n\n" . str_repeat("=", 50) . "\n";
            $report .= "MIGRATION SCRIPT:\n";
            $report .= str_repeat("=", 50) . "\n\n";
            $report .= $migration;
        }
        
        return $report;
    }
}

// Example usage
if (php_sapi_name() === 'cli') {
    // Command line usage
    echo "Database Schema Comparator\n";
    echo str_repeat("=", 50) . "\n\n";
    
    if ($argc < 3) {
        echo "Usage: php schema_comparator.php <schema1_file> <schema2_file> [output_file]\n";
        echo "\nExample:\n";
        echo "  php schema_comparator.php schema1.sql schema2.sql migration.sql\n";
        exit(1);
    }
    
    $schema1File = $argv[1];
    $schema2File = $argv[2];
    $outputFile = $argv[3] ?? null;
    
    if (!file_exists($schema1File)) {
        die("Error: Schema 1 file not found: $schema1File\n");
    }
    
    if (!file_exists($schema2File)) {
        die("Error: Schema 2 file not found: $schema2File\n");
    }
    
    $schema1 = file_get_contents($schema1File);
    $schema2 = file_get_contents($schema2File);
    
    $comparator = new SchemaComparator();
    $result = $comparator->compareSchemas($schema1, $schema2);
    
    echo $comparator->generateTextReport($result['differences'], $result['migration']);
    
    if ($outputFile) {
        file_put_contents($outputFile, $result['migration']);
        echo "\n\nMigration script saved to: $outputFile\n";
    }
    
} else {
    // Web usage example
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Schema Comparator</title>
        <style>
            body { font-family: Arial; max-width: 1200px; margin: 0 auto; padding: 20px; }
            textarea { width: 100%; height: 200px; font-family: monospace; }
            button { background: #3498db; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; }
            button:hover { background: #2980b9; }
            .container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <h1>Database Schema Comparator</h1>
        <form method="POST">
            <div class="container">
                <div>
                    <h3>Schema 1 (Target - Missing Columns)</h3>
                    <textarea name="schema1" required><?php echo isset($_POST['schema1']) ? htmlspecialchars($_POST['schema1']) : ''; ?></textarea>
                </div>
                <div>
                    <h3>Schema 2 (Source - Has New Columns)</h3>
                    <textarea name="schema2" required><?php echo isset($_POST['schema2']) ? htmlspecialchars($_POST['schema2']) : ''; ?></textarea>
                </div>
            </div>
            <button type="submit">Compare Schemas</button>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $comparator = new SchemaComparator();
            $result = $comparator->compareSchemas($_POST['schema1'], $_POST['schema2']);
            echo $comparator->generateHTMLReport($result['differences'], $result['migration']);
        }
        ?>
    </body>
    </html>
    <?php
}
?>