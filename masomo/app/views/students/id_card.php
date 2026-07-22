<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .id-card { page-break-inside: avoid; }
        }
        .id-card {
            width: 3.375in;
            height: 2.125in;
            border: 2px solid #000;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="no-print mb-4 text-center">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 mr-2">
            <i class="fas fa-print mr-2"></i>Print ID Card
        </button>
        <a href="<?php echo BASE_URL; ?>/students/show/<?php echo $student['id']; ?>" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Back to Student Details
        </a>
    </div>
    
    <div class="flex justify-center">
        <div class="id-card rounded-lg shadow-2xl">
            <div class="flex h-full">
                <!-- Left Side - Photo/QR Code -->
                <div class="w-1/3 flex flex-col items-center justify-center border-r-2 border-white border-opacity-30 pr-1.5">
                    <?php 
                    $photoUrl = !empty($student['photo']) ? getImageUrl($student['photo']) : null;
                    if ($photoUrl): 
                    ?>
                    <div class="mb-1.5">
                        <img src="<?php echo htmlspecialchars($photoUrl); ?>" 
                             alt="Student Photo" 
                             class="w-16 h-16 rounded-full border-2 border-white shadow-lg object-cover"
                             style="border-width: 2px;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-16 h-16 rounded-full border-2 border-white shadow-lg bg-white bg-opacity-20 flex items-center justify-center hidden">
                            <i class="fas fa-user text-white text-xl"></i>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="w-16 h-16 rounded-full border-2 border-white mb-1.5 bg-white bg-opacity-20 flex items-center justify-center shadow-lg" style="border-width: 2px;">
                        <i class="fas fa-user text-2xl text-white"></i>
                    </div>
                    <?php endif; ?>
                    <!-- QR Code -->
                    <div class="bg-white p-0.5 rounded">
                        <img src="<?php echo htmlspecialchars($qr_code_url); ?>" 
                             alt="QR Code" 
                             class="w-14 h-14"
                             title="Scan to verify student">
                    </div>
                </div>
                
                <!-- Right Side - Information -->
                <div class="w-2/3 pl-2 flex flex-col justify-between">
                    <div>
                        <div class="text-center mb-1">
                            <?php if (isset($school_logo)): ?>
                            <img src="<?php echo htmlspecialchars($school_logo); ?>" 
                                 alt="School Logo" 
                                 class="h-5 mx-auto mb-0.5 object-contain">
                            <?php endif; ?>
                            <h1 class="text-xs font-bold leading-tight"><?php echo htmlspecialchars($school_name); ?></h1>
                            <p class="text-xs opacity-90 mt-0.5">Student Identification Card</p>
                        </div>
                    </div>
                    
                    <div class="space-y-0.5 text-xs">
                        <div class="flex items-center">
                            <span class="font-semibold w-16">Name:</span>
                            <span class="flex-1 truncate"><?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']); ?></span>
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold w-16">Adm No:</span>
                            <span class="flex-1"><?php echo htmlspecialchars($student['admission_number']); ?></span>
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold w-16">Grade:</span>
                            <span class="flex-1"><?php echo htmlspecialchars($student['grade_display_name'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                    
                    <div class="mt-1 pt-1 border-t border-white border-opacity-30">
                        <div class="text-center text-xs">
                            <p class="opacity-75">Valid until: <?php echo date('Y'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

