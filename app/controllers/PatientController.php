<?php
namespace App\Controllers;

use App\Models\Patient;
use App\Helpers\Response;

class PatientController {
    private $patientModel;

    public function __construct() {
        $this->patientModel = new Patient();
    }

    // 1. Get all patients belonging exclusively to the logged-in user
    public function index($request) {
        $userId = $request['user']['user_id'] ?? null;

        if (!$userId) {
            Response::json(401, false, "Unauthorized. Valid token payload required.");
        }

        $patients = $this->patientModel->getAll($userId);
        Response::json(200, true, "Patients retrieved successfully.", $patients);
    }

    // 2. Fetch a single patient record with cross-user ownership verification
    public function show($request) {
        $patientId = $request['params'][0] ?? null;
        $userId = $request['user']['user_id'] ?? null; 

        if (!$patientId || !$userId) {
            Response::json(400, false, "Invalid request parameters.");
        }

        $patient = $this->patientModel->findById($patientId, $userId);

        if (!$patient) {
            Response::json(404, false, "Patient record not found or access denied.");
        }

        Response::json(200, true, "Patient record loaded securely.", $patient);
    }

    // 3. Create a patient profile linked directly to the creator's user ID
    public function store($request) {
        $userId = $request['user']['user_id'] ?? null;
        $bodyData = $request['body'] ?? null; 

        if (!$bodyData || empty($bodyData['name'])) {
            Response::json(400, false, "Invalid request. Patient name is required.");
        }

        $newId = $this->patientModel->create($bodyData, $userId);
        
        if (!$newId) {
            Response::json(500, false, "Failed to record patient profile due to a server error.");
        }
        
        $freshPatientRecord = $this->patientModel->findById($newId, $userId);
        Response::json(201, true, "Patient profile recorded successfully.", $freshPatientRecord);
    }

    // 4. Modify an existing patient profile after verifying access credentials
    public function update($request) { 
        $id = $request['params'][0] ?? null;
        $body = $request['body'] ?? null;
        $userId = $request['user']['user_id'] ?? null;

        if (!$id || !$userId || !$this->patientModel->findById($id, $userId)) {
            Response::json(404, false, "Patient record profile could not be located or access denied.");
        }

        if (empty($body['name']) || empty($body['age']) || empty($body['gender']) || empty($body['phone'])) {
            Response::json(400, false, "Update payload fields cannot be left blank.");
        }

        if ($this->patientModel->update($id, $body, $userId)) {
            $updatedPatient = $this->patientModel->findById($id, $userId);
            Response::json(200, true, "Patient record profile refreshed successfully.", $updatedPatient);
        }
        
        Response::json(500, false, "Failed to refresh patient profile.");
    }

    // 5. Permanently remove an assigned data row asset matching user permissions
    public function destroy($request) { 
        $id = $request['params'][0] ?? null;
        $userId = $request['user']['user_id'] ?? null;

        if (!$id || !$userId || !$this->patientModel->findById($id, $userId)) {
            Response::json(404, false, "Patient profile matches no accessible structural asset.");
        }

        if ($this->patientModel->delete($id, $userId)) {
            Response::json(200, true, "Patient record purged successfully.");
        }
        
        Response::json(500, false, "Failed to purge patient record.");
    }
} // <── FIX: This final closing brace completes the entire class structure!