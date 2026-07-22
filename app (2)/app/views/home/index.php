<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? APP_NAME; ?> - Comprehensive School Management System</title>
    <link rel="icon" type="image/png" href="<?php echo getSystemLogo(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#059669'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-section {
            position: relative;
            <?php 
            $headerImage = getImageUrl('header.jpg');
            if ($headerImage): 
            ?>
            background-image: url('<?php echo htmlspecialchars($headerImage); ?>');
            <?php else: ?>
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            <?php endif; ?>
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .cta-section {
            position: relative;
            background-image: url('<?php echo BASE_URL; ?>/public/uploads/footer.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .cta-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.80) 0%, rgba(118, 75, 162, 0.80) 100%);
            z-index: 1;
        }
        .cta-content {
            position: relative;
            z-index: 2;
        }
        .services-section {
            position: relative;
            background-image: url('<?php echo BASE_URL; ?>/public/uploads/services.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .services-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom right, rgba(239, 246, 255, 0.75) 0%, rgba(224, 231, 255, 0.75) 100%);
            z-index: 1;
        }
        .services-content {
            position: relative;
            z-index: 2;
        }
        .hover-scale {
            transition: transform 0.3s ease;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="<?php echo getSystemLogo(); ?>" alt="Logo" class="h-10 w-10">
                    <span class="text-xl font-bold text-gray-800"><?php echo APP_NAME; ?></span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo BASE_URL; ?>/auth/login" 
                       class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-white py-20 relative">
        <div class="hero-overlay"></div>
        <div class="container mx-auto px-4 text-center fade-in-up hero-content">
            <div class="max-w-4xl mx-auto">
                <img src="<?php echo getSystemLogo(); ?>" alt="Logo" class="mx-auto h-24 w-24 mb-8 rounded-full bg-white p-2 shadow-lg">
                <h1 class="text-5xl md:text-6xl font-bold mb-6 drop-shadow-lg"><?php echo APP_NAME; ?></h1>
                <p class="text-xl md:text-2xl mb-4 text-blue-100 drop-shadow-md">CBC-Compliant School Management System</p>
                <p class="text-lg mb-8 text-blue-200 drop-shadow-md">Comprehensive solution for Kenyan Primary Schools</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo BASE_URL; ?>/auth/login" 
                       class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition inline-block hover-scale shadow-lg">
                        <i class="fas fa-rocket mr-2"></i>Get Started
                    </a>
                    <a href="#features" 
                       class="bg-blue-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-900 transition inline-block hover-scale shadow-lg">
                        <i class="fas fa-info-circle mr-2"></i>Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Comprehensive Modules & Features</h2>
                <p class="text-xl text-gray-600">Everything you need to manage your school efficiently</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Student Management -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border border-gray-100">
                    <div class="text-blue-600 text-5xl mb-4">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Student Management</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Complete student records</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Auto-generated admission numbers</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>UPI (Unique Personal Identifier)</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Parent/guardian linking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Advanced search & filtering</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Status management (Active, Alumni, etc.)</li>
                    </ul>
                </div>

                <!-- CBC Academic Module -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border border-gray-100">
                    <div class="text-purple-600 text-5xl mb-4">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">CBC Curriculum</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>PP1, PP2, Grade 1-6 support</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Learning areas per grade</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Strands & sub-strands</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Competency-based assessments</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Continuous assessment tracking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>CBC-compliant report cards</li>
                    </ul>
                </div>

                <!-- Fee Management -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border border-gray-100">
                    <div class="text-green-600 text-5xl mb-4">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Fee Management</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Flexible fee structure per grade</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Automatic invoice generation</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>M-Pesa PayBill integration</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Automatic payment reconciliation</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Real-time balance tracking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Comprehensive financial reports</li>
                    </ul>
                </div>

                <!-- Attendance System -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border border-gray-100">
                    <div class="text-orange-600 text-5xl mb-4">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Attendance Tracking</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Daily attendance marking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Bulk class attendance</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Multiple status types</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Monthly attendance summaries</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Percentage calculations</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Detailed attendance reports</li>
                    </ul>
                </div>

                <!-- Teacher Management -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border border-gray-100">
                    <div class="text-red-600 text-5xl mb-4">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Teacher Management</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Complete teacher profiles</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>TSC number tracking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Class assignments</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>User account integration</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Teacher attendance tracking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Performance monitoring</li>
                    </ul>
                </div>

                <!-- Parent Portal -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border border-gray-100">
                    <div class="text-indigo-600 text-5xl mb-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Parent Portal</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Dedicated parent dashboard</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>View all children</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Academic progress tracking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Attendance summaries</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Fee balance & payment history</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>School announcements</li>
                    </ul>
                </div>

                <!-- Communication -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border border-gray-100">
                    <div class="text-teal-600 text-5xl mb-4">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Communication</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>School announcements</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Targeted messaging</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Priority levels</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>SMS gateway integration</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Payment confirmation SMS</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Automated notifications</li>
                    </ul>
                </div>

                <!-- Reports & Analytics -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border border-gray-100">
                    <div class="text-pink-600 text-5xl mb-4">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Reports & Analytics</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Student reports</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Attendance reports</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Financial reports</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Academic performance reports</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Print-ready formats</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Date range filtering</li>
                    </ul>
                </div>

                <!-- Security & Access Control -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border border-gray-100">
                    <div class="text-yellow-600 text-5xl mb-4">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Security & Access</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Role-based access control</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>7 user roles with permissions</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>CSRF protection</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Secure password hashing</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>SQL injection prevention</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Session management</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-20 services-section relative">
        <div class="services-overlay"></div>
        <div class="container mx-auto px-4 services-content">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4 drop-shadow-sm">Our Services</h2>
                <p class="text-xl text-gray-700 drop-shadow-sm">Comprehensive solutions tailored for Kenyan schools</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg p-6 shadow-md text-center hover-scale">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">M-Pesa Integration</h3>
                    <p class="text-gray-600">Seamless PayBill payment processing with automatic reconciliation and SMS confirmations</p>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-md text-center hover-scale">
                    <div class="text-green-600 text-4xl mb-4">
                        <i class="fas fa-sms"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">SMS Notifications</h3>
                    <p class="text-gray-600">Automated SMS alerts for payments, announcements, and important updates</p>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-md text-center hover-scale">
                    <div class="text-purple-600 text-4xl mb-4">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Cloud-Ready</h3>
                    <p class="text-gray-600">Deploy on-premise or cloud infrastructure with flexible hosting options</p>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-md text-center hover-scale">
                    <div class="text-orange-600 text-4xl mb-4">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">24/7 Support</h3>
                    <p class="text-gray-600">Comprehensive documentation and support resources for smooth operations</p>
                </div>
            </div>
        </div>
    </section>

    <!-- User Roles Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">User Roles & Permissions</h2>
                <p class="text-xl text-gray-600">Tailored access for different user types</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-6 shadow-lg">
                    <div class="text-4xl mb-4">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Super Admin</h3>
                    <p class="text-blue-100">Full system access, user management, and system configuration</p>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg p-6 shadow-lg">
                    <div class="text-4xl mb-4">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">School Admin</h3>
                    <p class="text-green-100">Student & teacher management, fees, reports, and announcements</p>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg p-6 shadow-lg">
                    <div class="text-4xl mb-4">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Head Teacher</h3>
                    <p class="text-purple-100">View students/teachers, manage assessments, attendance, and reports</p>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg p-6 shadow-lg">
                    <div class="text-4xl mb-4">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Teacher</h3>
                    <p class="text-orange-100">View students, mark attendance, create assessments, view announcements</p>
                </div>

                <div class="bg-gradient-to-br from-teal-500 to-teal-600 text-white rounded-lg p-6 shadow-lg">
                    <div class="text-4xl mb-4">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Bursar</h3>
                    <p class="text-teal-100">Manage fees, process payments, M-Pesa integration, financial reports</p>
                </div>

                <div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-lg p-6 shadow-lg">
                    <div class="text-4xl mb-4">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Parent</h3>
                    <p class="text-pink-100">View children, academic progress, attendance, fee balances, announcements</p>
                </div>

                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-lg p-6 shadow-lg">
                    <div class="text-4xl mb-4">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Student</h3>
                    <p class="text-indigo-100">View own assessments, attendance records, and school announcements</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Why Choose <?php echo APP_NAME; ?>?</h2>
                <p class="text-xl text-gray-600">Benefits that make a difference</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="bg-white rounded-lg p-8 shadow-md text-center">
                    <div class="text-blue-600 text-5xl mb-4">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">CBC Compliant</h3>
                    <p class="text-gray-600">Fully aligned with Kenya's Competency-Based Curriculum requirements</p>
                </div>

                <div class="bg-white rounded-lg p-8 shadow-md text-center">
                    <div class="text-green-600 text-5xl mb-4">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Efficient & Fast</h3>
                    <p class="text-gray-600">Streamlined workflows reduce administrative time and increase productivity</p>
                </div>

                <div class="bg-white rounded-lg p-8 shadow-md text-center">
                    <div class="text-purple-600 text-5xl mb-4">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Secure & Reliable</h3>
                    <p class="text-gray-600">Enterprise-grade security with role-based access control and data protection</p>
                </div>

                <div class="bg-white rounded-lg p-8 shadow-md text-center">
                    <div class="text-orange-600 text-5xl mb-4">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Mobile Friendly</h3>
                    <p class="text-gray-600">Responsive design works seamlessly on desktop, tablet, and mobile devices</p>
                </div>

                <div class="bg-white rounded-lg p-8 shadow-md text-center">
                    <div class="text-teal-600 text-5xl mb-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Data-Driven</h3>
                    <p class="text-gray-600">Comprehensive reports and analytics for informed decision-making</p>
                </div>

                <div class="bg-white rounded-lg p-8 shadow-md text-center">
                    <div class="text-pink-600 text-5xl mb-4">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">User-Friendly</h3>
                    <p class="text-gray-600">Intuitive interface designed for ease of use by all staff members</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Technology Stack -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Built with Modern Technology</h2>
                <p class="text-xl text-gray-600">Reliable, scalable, and maintainable</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                <div class="bg-gray-50 rounded-lg p-6 text-center border border-gray-200">
                    <div class="text-blue-600 text-4xl mb-3">
                        <i class="fab fa-php"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">PHP 7.4+</h3>
                    <p class="text-gray-600 text-sm">Object-Oriented MVC Architecture</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 text-center border border-gray-200">
                    <div class="text-green-600 text-4xl mb-3">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">MySQL</h3>
                    <p class="text-gray-600 text-sm">Robust database management</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 text-center border border-gray-200">
                    <div class="text-purple-600 text-4xl mb-3">
                        <i class="fab fa-html5"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Tailwind CSS</h3>
                    <p class="text-gray-600 text-sm">Modern, responsive UI framework</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 text-center border border-gray-200">
                    <div class="text-orange-600 text-4xl mb-3">
                        <i class="fab fa-js"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Vanilla JavaScript</h3>
                    <p class="text-gray-600 text-sm">Fast, lightweight client-side</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 cta-section text-white relative">
        <div class="cta-overlay"></div>
        <div class="container mx-auto px-4 text-center cta-content">
            <h2 class="text-4xl font-bold mb-4 drop-shadow-lg">Ready to Transform Your School Management?</h2>
            <p class="text-xl mb-8 text-blue-100 drop-shadow-md">Join schools across Kenya using <?php echo APP_NAME; ?> for efficient school administration</p>
            <a href="<?php echo BASE_URL; ?>/auth/login" 
               class="bg-white text-blue-600 px-10 py-4 rounded-lg font-bold text-lg hover:bg-gray-100 transition inline-block hover-scale shadow-lg">
                <i class="fas fa-rocket mr-2"></i>Get Started Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="<?php echo getSystemLogo(); ?>" alt="Logo" class="h-10 w-10">
                        <span class="text-xl font-bold text-white"><?php echo APP_NAME; ?></span>
                    </div>
                    <p class="text-gray-400">Comprehensive CBC-compliant school management system for Kenyan primary schools.</p>
                </div>

                <div>
                    <h3 class="text-white font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/auth/login" class="hover:text-white transition">Login</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-white font-bold mb-4">Contact</h3>
                    <p class="text-gray-400">For support and inquiries, please contact your system administrator.</p>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
