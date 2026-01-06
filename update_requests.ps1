# Script to update all request files to extend BaseRequest
$requestFiles = Get-ChildItem -Path "app/Http/Requests" -Recurse -Filter "*Request.php" | Where-Object { $_.Name -ne "BaseRequest.php" }

foreach ($file in $requestFiles) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    
    if ($content -match 'extends FormRequest') {
        # Remove the FormRequest use statement
        $content = $content -replace '(?m)^use Illuminate\\Foundation\\Http\\FormRequest;$\r?\n', ''
        
        # Update class declaration
        $content = $content -replace 'extends FormRequest', 'extends BaseRequest'
        
        # If it's in a subdirectory, add the BaseRequest import
        if ($file.DirectoryName -match 'Product') {
            if ($content -notmatch 'use App\\Http\\Requests\\BaseRequest;') {
                # Find the namespace line and add import after it
                $content = $content -replace '(namespace App\\Http\\Requests\\Product;)', "`$1`n`nuse App\Http\Requests\BaseRequest;"
            }
        }
        
        # Write back
        [System.IO.File]::WriteAllText($file.FullName, $content, [System.Text.Encoding]::UTF8)
        Write-Host "Updated: $($file.Name)"
    }
}

Write-Host "`nUpdate complete!"


