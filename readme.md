function.php

```
add_action( 'plugins_loaded', function() {
    \DHead_WP\DH_Toolkit_Manager::register_all(); 
});
```