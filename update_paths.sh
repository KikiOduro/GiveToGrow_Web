#!/bin/bash
# Script to update file paths after moving views to views/ folder

cd /Applications/MAMP/htdocs/GiveToGrow_Web/views

echo "Updating paths in view files..."

# Update relative paths for assets, actions, admin, login folders
find . -name "*.php" -type f -exec sed -i '' 's|src="assets/|src="../assets/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|href="assets/|href="../assets/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|href="actions/|href="../actions/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|action="actions/|action="../actions/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|href="admin/|href="../admin/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|Location: login/|Location: ../login/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|href="login/|href="../login/|g' {} \;

# Update require/include statements
find . -name "*.php" -type f -exec sed -i '' 's|__DIR__ . \/settings/|__DIR__ . "/../settings/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|__DIR__ . \/controllers/|__DIR__ . "/../controllers/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|__DIR__ . \/models/|__DIR__ . "/../models/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|require_once "settings/|require_once "../settings/|g' {} \;
find . -name "*.php" -type f -exec sed -i '' 's|require_once "controllers/|require_once "../controllers/|g' {} \;

echo "Path updates complete!"
