<?php
/**
 * Home Controller
 * Handles home page and public routes
 */

class HomeController extends Controller {
    
    /**
     * Home page - Landing page
     */
    public function index() {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Welcome - ' . APP_NAME
        ];
        
        // Use viewPartial since the landing page has its own complete HTML structure
        $this->viewPartial('home/index', $data);
    }
}

