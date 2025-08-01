<?php
// Simple form test page
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form Test</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 300px; padding: 5px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .success { color: green; background: #d4edda; padding: 10px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Property Form Test</h2>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="success">✅ Form submitted successfully! Data received:</div>
        <pre><?php print_r($_POST); ?></pre>
        
        <p><a href="add-property.php">Go to real form</a></p>
    <?php else: ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Title:</label>
            <input type="text" name="title" value="Test Property" required>
        </div>
        
        <div class="form-group">
            <label>Property Type:</label>
            <select name="property_type" required>
                <option value="apartment">Apartment</option>
                <option value="house">House</option>
                <option value="office">Office</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Category:</label>
            <select name="category" required>
                <option value="apartment">Apartment</option>
                <option value="house">House</option>
                <option value="office">Office</option>
                <option value="shop">Shop</option>
                <option value="warehouse">Warehouse</option>
                <option value="land">Land</option>
                <option value="villa">Villa</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Price:</label>
            <input type="number" name="price" value="500000" required>
        </div>
        
        <div class="form-group">
            <label>Area Gross (m²):</label>
            <input type="number" name="area_gross" value="120">
        </div>
        
        <div class="form-group">
            <label>Area Net (m²):</label>
            <input type="number" name="area_net" value="100">
        </div>
        
        <div class="form-group">
            <label>Room Count:</label>
            <input type="text" name="room_count" value="3+1">
        </div>
        
        <div class="form-group">
            <label>Bedrooms:</label>
            <input type="number" name="bedroom_count" value="3">
        </div>
        
        <div class="form-group">
            <label>Bathrooms:</label>
            <input type="number" name="bathroom_count" value="2">
        </div>
        
        <div class="form-group">
            <label>City:</label>
            <input type="text" name="city" value="Istanbul">
        </div>
        
        <div class="form-group">
            <label>District:</label>
            <input type="text" name="district" value="Besiktas">
        </div>
        
        <div class="form-group">
            <label>Neighborhood:</label>
            <input type="text" name="neighborhood" value="Etiler">
        </div>
        
        <div class="form-group">
            <label>Address:</label>
            <textarea name="address">Test Address 123</textarea>
        </div>
        
        <div class="form-group">
            <label>Floor:</label>
            <input type="number" name="floor" value="5">
        </div>
        
        <div class="form-group">
            <label>Total Floors:</label>
            <input type="number" name="total_floors" value="10">
        </div>
        
        <div class="form-group">
            <label>Building Age:</label>
            <input type="number" name="building_age" value="5">
        </div>
        
        <div class="form-group">
            <label>Heating:</label>
            <select name="heating">
                <option value="central">Central</option>
                <option value="individual">Individual</option>
                <option value="none">None</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Elevator:</label>
            <select name="elevator">
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Parking:</label>
            <select name="parking">
                <option value="garage">Garage</option>
                <option value="street">Street</option>
                <option value="none">None</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Kitchen:</label>
            <select name="kitchen">
                <option value="american">American</option>
                <option value="separate">Separate</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Balcony Count:</label>
            <input type="number" name="balcony_count" value="2">
        </div>
        
        <div class="form-group">
            <label>Description:</label>
            <textarea name="description">Test property description</textarea>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="furnished" value="1"> Furnished
            </label>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_featured" value="1"> Featured
            </label>
        </div>
        
        <div class="form-group">
            <label>Location Type:</label>
            <select name="location_type">
                <option value="site">Site</option>
                <option value="standalone">Standalone</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Site Name:</label>
            <input type="text" name="site_name" value="Test Site">
        </div>
        
        <button type="submit">Test Submit</button>
    </form>
    
    <?php endif; ?>
</body>
</html>
