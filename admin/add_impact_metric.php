<?php
session_start();
require_once __DIR__ . '/../settings/admin_check.php';
require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();

// Fetch all schools for dropdown
$schools = $db->db_fetch_all("SELECT school_id, school_name FROM schools WHERE status = 'active' ORDER BY school_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id = intval($_POST['school_id']);
    $metric_type = $_POST['metric_type'];
    $metric_value = floatval($_POST['metric_value']);
    $metric_unit = trim($_POST['metric_unit']);
    $measurement_date = $_POST['measurement_date'];
    $notes = trim($_POST['notes']);
    
    if ($school_id && $metric_type && $metric_value >= 0) {
        $insert_query = "
            INSERT INTO impact_metrics (school_id, metric_type, metric_value, metric_unit, measurement_date, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        if ($db->db_query($insert_query, [$school_id, $metric_type, $metric_value, $metric_unit, $measurement_date, $notes])) {
            $_SESSION['success_message'] = "Impact metric added successfully!";
            header("Location: add_impact_metric.php");
            exit();
        } else {
            $error_message = "Failed to add metric. Please try again.";
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Fetch recent metrics
$recent_metrics = $db->db_fetch_all("
    SELECT im.*, s.school_name
    FROM impact_metrics im
    JOIN schools s ON im.school_id = s.school_id
    ORDER BY im.recorded_at DESC
    LIMIT 15
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Impact Metrics - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { "primary": "#A4B8A4" },
                    fontFamily: { "display": ["Lexend", "sans-serif"] }
                }
            }
        }
        
        function updateUnitSuggestion() {
            const metricType = document.getElementById('metric_type').value;
            const unitInput = document.getElementById('metric_unit');
            
            const suggestions = {
                'students_benefited': 'students',
                'grade_improvement': '%',
                'attendance_increase': '%',
                'items_distributed': 'items',
                'other': ''
            };
            
            unitInput.value = suggestions[metricType] || '';
        }
    </script>
</head>
<body class="bg-gray-50 font-display">
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <div class="ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-8">Add Impact Metrics</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Add Metric Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">School *</label>
                        <select name="school_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="">Select a school</option>
                            <?php foreach ($schools as $school): ?>
                            <option value="<?php echo $school['school_id']; ?>"><?php echo htmlspecialchars($school['school_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Metric Type *</label>
                        <select name="metric_type" id="metric_type" required onchange="updateUnitSuggestion()" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="">Select metric type</option>
                            <option value="students_benefited">Students Benefited</option>
                            <option value="grade_improvement">Grade Improvement</option>
                            <option value="attendance_increase">Attendance Increase</option>
                            <option value="items_distributed">Items Distributed</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Value *</label>
                            <input type="number" name="metric_value" required step="0.01" min="0"
                                   placeholder="e.g., 150"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"/>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2">Unit *</label>
                            <input type="text" name="metric_unit" id="metric_unit" required maxlength="50"
                                   placeholder="e.g., students, %, items"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"/>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Measurement Date *</label>
                        <input type="date" name="measurement_date" required 
                               max="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"/>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Notes (Optional)</label>
                        <textarea name="notes" rows="3"
                                  placeholder="Additional context about this metric..."
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-bold hover:opacity-90">
                        Add Metric
                    </button>
                </form>
            </div>
            
            <!-- Recent Metrics -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Recent Metrics</h2>
                <?php if (empty($recent_metrics)): ?>
                <p class="text-gray-500 text-center py-8">No metrics added yet.</p>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">School</th>
                                <th class="text-left py-2">Metric</th>
                                <th class="text-right py-2">Value</th>
                                <th class="text-left py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_metrics as $metric): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 text-sm"><?php echo htmlspecialchars($metric['school_name']); ?></td>
                                <td class="py-2 text-sm">
                                    <span class="bg-primary/20 text-primary px-2 py-1 rounded text-xs">
                                        <?php echo str_replace('_', ' ', $metric['metric_type']); ?>
                                    </span>
                                </td>
                                <td class="py-2 text-sm text-right font-bold">
                                    <?php echo number_format($metric['metric_value'], 0); ?> <?php echo htmlspecialchars($metric['metric_unit']); ?>
                                </td>
                                <td class="py-2 text-sm text-gray-600">
                                    <?php echo date('M d, Y', strtotime($metric['measurement_date'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
