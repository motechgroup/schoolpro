<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended - SchoolPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-red-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 fade-in">
        <div class="text-center">
            <!-- Icon -->
            <div class="mx-auto w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-ban text-5xl text-red-600"></i>
            </div>
            
            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-3">Account Suspended</h1>
            
            <!-- Message -->
            <p class="text-gray-600 mb-6 leading-relaxed">
                Your school account has been temporarily suspended by the administrator.
            </p>
            
            <?php if ($schoolStatus && !empty($schoolStatus['notes'])): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 text-left">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Reason:</strong> <?php echo htmlspecialchars($schoolStatus['notes']); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Contact Information -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-blue-600"></i>
                    <strong>Need Help?</strong>
                </p>
                <p class="text-sm text-gray-600">
                    Please contact the system administrator to resolve this issue.
                </p>
            </div>
            
            <!-- School Info (if available) -->
            <?php if ($schoolStatus): ?>
            <div class="border-t pt-4 mt-4">
                <p class="text-xs text-gray-500">
                    <i class="fas fa-school mr-1"></i>
                    <?php echo htmlspecialchars($schoolStatus['school_name'] ?? 'School'); ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

