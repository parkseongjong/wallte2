<fieldset>
    <div class="form-group">
        <label for="l_name">Name*</label>
        <input type="text" name="name" value="<?php echo $edit ? $users['name'] : ''; ?>" placeholder="Name" class="form-control" required="required" id="name">
    </div> 

    <div class="form-group">
        <label for="phone">Phone</label>
            <input name="phone" value="<?php echo $edit ? $users['phone'] : ''; ?>" placeholder="phone" class="form-control"  type="text" id="phone">
    </div> 

    
    <div class="form-group text-center">
        <label></label>
        <button type="submit" class="btn btn-warning" >Save <span class="glyphicon glyphicon-send"></span></button>
    </div>            
</fieldset>