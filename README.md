# WordPress-Libs

This repository will contain general mince I've had to code either to supplement the WordPress API or, more usually, fix some of it's more egregious problems.

Current items include:

* Binary-safe wrapper for existing meta functions (incomplete); includes binary-safe versions of add_post_meta(), update_post_meta(), get_post_meta(), delete_post_meta().  Essentially this is just doing a serialization and then base64 encoding to ensure the meta_value entities don't get mangled MySQL character fields.


 
