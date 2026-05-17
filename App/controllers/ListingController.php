<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;
use Framework\Session;
use Framework\Authorization;

class ListingController
{
    protected $db;

    public function __construct()
    {
        // Configuration array para sa direktang koneksyon sa database ng Laragon
        $config = [
            'host'     => 'localhost',
            'port'     => '3306',
            'dbname'   => 'ws03',
            'username' => 'root',
            'password' => ''
        ];

        $this->db = new Database($config);
    }

    /**
     * Display all listings
     * * @return void
     */
    public function index()
    {
        $listings = $this->db->query('SELECT * FROM listings ORDER BY created_at DESC')->fetchAll();

        loadView('listings/index', ['listings' => $listings]);
    }

    /**
     * Show the create listing form
     * * @return void
     */
    public function create()
    {
        loadView('listings/create');
    }

    /**
     * Show a specific listing details
     * * @param array $params
     * @return void
     */
    public function show($params)
    {
        $id = $params['id'] ?? '';
        $params = [
            'id' => $id
        ];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        // Check if listing exist
        if (!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        loadView('listings/show', [
            'listing' => $listing
        ]);
    }

    /**
     * Store data in database
     * * @return void
     */
    public function store()
    {
        $allowedFields = [
            'title',
            'description',
            'salary',
            'tags',
            'company',
            'address',
            'city',
            'state',
            'phone',
            'email',
            'requirements',
            'benefits'
        ];

        $newListingData = array_intersect_key($_POST, array_flip($allowedFields));

        // Safe fallback check para hindi mag-null ang user_id kapag nag-te-test habang naka-logout
        $userSession = Session::get('user');
        $newListingData['user_id'] = isset($userSession['id']) ? $userSession['id'] : 1;

        $newListingData = array_map('sanitize', $newListingData);

        $requiredFields = [
            'title',
            'description',
            'salary',
            'email',
            'city',
            'state'
        ];

        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($newListingData[$field]) || !Validation::string($newListingData[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }

        if (!empty($errors)) {
            // Reload view with errors
            loadView('listings/create', [
                'errors' => $errors,
                'listing' => $newListingData
            ]);
        } else {
            $fields = [];

            foreach ($newListingData as $field => $val) {
                $fields[] = $field;
            }

            $fields = implode(', ', $fields);

            $values = []; 

            foreach ($newListingData as $field => $val) {
                // Convert empty strings to null
                if ($val === '') {
                    $newListingData[$field] = null;
                }
                $values[] = ':' . $field;
            }
            $values = implode(', ', $values);

            $query = "INSERT INTO listings ({$fields}) VALUES ({$values})";

            $this->db->query($query, $newListingData);

            // TINAMAAN: Binago patungong Session::set mula sa dating setFlashMessage para hindi mag-error
            Session::set('success_message', 'Listing created successfully');

            redirect('/listings');
        }
    }

    /**
     * Delete a listing
     * * @param array $params
     * @return void
     */
    public function destroy($params)
    {
        $id = $params['id'];

        $params = [
            'id' => $id
        ];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        // Check if listing exist
        if (!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        // Authorization check
        if (!Authorization::isOwner($listing->user_id)) {
            Session::set('error_message', 'You are not authorized to delete this listing');
            return redirect('/listings/' . $listing->id);
        }

        $this->db->query('DELETE FROM listings WHERE id = :id', $params);

        // TINAMAAN: Binago mula sa setFlashMessage patungong Session::set
        Session::set('success_message', 'Listing deleted successfully');

        redirect('/listings');
    }

    /**
     * Show the edit listing form
     * * @param array $params
     * @return void
     */
    public function edit($params)
    {
        $id = $params['id'] ?? '';
        $params = [
            'id' => $id
        ];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        // Check if listing exist
        if (!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        // Authorization check
        if (!Authorization::isOwner($listing->user_id)) {
            Session::set('error_message', 'You are not authorized to update this listing');
            return redirect('/listings/' . $listing->id);
        }

        loadView('listings/edit', [
            'listing' => $listing
        ]);
    }

    /**
     * Update listing
     * * @param array $params
     * @return void
     */
    public function update($params)
    {
        $id = $params['id'] ?? '';
        $params = [
            'id' => $id
        ];

        $listing = $this->db->query('SELECT * FROM listings WHERE id = :id', $params)->fetch();

        // Check if listing exist
        if (!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        // Authorization check
        if (!Authorization::isOwner($listing->user_id)) {
            Session::set('error_message', 'You are not authorized to update this listing');
            return redirect('/listings/' . $listing->id);
        }

        $allowedFields = [
            'title',
            'description',
            'salary',
            'tags',
            'company',
            'address',
            'city',
            'state',
            'phone',
            'email',
            'requirements',
            'benefits'
        ];

        $updateValues = array_intersect_key($_POST, array_flip($allowedFields));
        $updateValues = array_map('sanitize', $updateValues);

        $requiredFields = [
            'title',
            'description',
            'salary',
            'email',
            'city',
            'state'
        ];

        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($updateValues[$field]) || !Validation::string($updateValues[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }

        if (!empty($errors)) {
            $updateValues['id'] = $id;
            loadView('listings/edit', [
                'listing' => (object)$updateValues,
                'errors' => $errors
            ]);
            exit;
        } else {
            // Submit to DB
            $updateFields = [];

            foreach (array_keys($updateValues) as $field) {
                if ($updateValues[$field] === '') {
                    $updateValues[$field] = null;
                }
                $updateFields[] = "{$field} = :{$field}";
            }

            $updateFields = implode(', ', $updateFields);

            $updateQuery = "UPDATE listings SET $updateFields WHERE id = :id";

            $updateValues['id'] = $id;

            $this->db->query($updateQuery, $updateValues);

            // TINAMAAN: Binago mula sa setFlashMessage patungong Session::set
            Session::set('success_message', 'Listing updated successfully');

            redirect('/listings/' . $id);
        }
    }

    /**
     * Search listings by keyword and location
     * * @return void
     */
    public function search()
    {
        $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';

        $query = "SELECT * FROM listings WHERE (title LIKE :keywords OR description LIKE :keywords OR tags LIKE :keywords OR company LIKE :keywords) AND (city LIKE :location OR state LIKE :location)";

        $params = [
            'keywords' => "%{$keywords}%",
            'location' => "%{$location}%"
        ];

        $listings = $this->db->query($query, $params)->fetchAll();

        loadView('listings/index', [
            'listings' => $listings,
            'keywords' => $keywords,
            'location' => $location
        ]);
    }
}