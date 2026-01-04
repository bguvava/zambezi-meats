======================================
#ISSUES FOUND THAT NEED TO BE FIXED:
======================================
- Required color palette: #CF0D0F, #F6211F, #EFEFEF, #6F6F6F, #4D4B4C. Official logos are in the ".github/" folder (official_logo.png, official_logo-white.png, official_logo_landscape.png, official_logo_landscape_white.png), choose the best based on the placement - the logo files to the appropriate folder.
- THIS SYSTEM MUST BE FAST, SECURE, ROBUST, AND OPERATE 99.9% UPTIME WITH 0.000% ERRORS. HANDLE PAYMENTS SECURELY ALL THE TIME. 
======================================

#GLOBAL ISSUES:
1. Front public website:
## on load there is a browser console log error message which says:
------------------
auth.js:87  GET http://localhost:8000/api/v1/auth/user 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=87907051:1984
xhr @ axios.js?v=87907051:1894
dispatchRequest @ axios.js?v=87907051:2363
Promise.then
_request @ axios.js?v=87907051:2557
request @ axios.js?v=87907051:2486
Axios$1.<computed> @ axios.js?v=87907051:2594
wrap @ axios.js?v=87907051:13
fetchUser @ auth.js:87
initialize @ auth.js:59
await in initialize
wrappedAction @ pinia.js?v=87907051:4795
store.<computed> @ pinia.js?v=87907051:4482
(anonymous) @ main.js:51
------------------
## on homepage hero section, i want the company name and logo to be more prominent and visible
## homepage: on Featured products section, the quick view and add to wishlist buttons are not working or responding.
## homepage: on Featured products section, all the products displayed there must be dynamically fetched from the database as featured/best selling products, not hardcoded products.
## homepage: Subscriptions: all the subscriptions requests submitted from this section must be stored into the database and be viewable from the admin/staff dashboard via messages
## homepage: on contact us sections, redesign it and change it to a map section which shows service areas near the proximity of the company address: 6/1053 Old Princes Highway, Engadine, NSW 2233, Australia. Add areas that are in the 50km radius. Remove the contact us form and on use the one on the contact us page.

##About Us page: add more sections as on home page [about us section]

##Contact us page: add a hero section same as on the about us page to make the design feel the same. 
##Contact us page: add a honeypot spam filter so that bots are not used to spam/phish the business.   
##Contact us page: all the contact form submissions must be stored into the database and viewable by the admin/staff via their dashboards 

##Shop page: the categories on the left panel are too many reduce them to only main categories, dynamically fetch these categories from the database do not hard code them. 
##Shop page: get real product images from the free sites and download them into the product codebase "frontend/public/iamges". The product images must match the real products and must also be stored into the database and fetched dynamically. Remove all placeholder images and use the reall images. 

2. This system is mainly for Australian target market, so everything must be localised to Australia: currency, locations, language, etc. The default currency must be AUD$, and the default location must be in the radius of the comapny offices/location. 

3. Session persistance issues:
## when customer logs in and opens the shop, when they try to access their dashboard, they are asked to login again and they are automatically redirected to the "/login?session_expired=true" but they will be currecntly logged in. This happening for all user roles. 
## when a differenct role logs in they
## on broswer refresh from the dashboard (all roles), the user is automatically logged out and redirected to the login page. the session must stay persistant.

4. Session Locked screen:
## redesign the session lock screen its height is too long (check the screenshot .github/BUGS/lockout.png), most screens will not be able to dsiplay it without zooming. it must be minimalist and centered on the screen. 
## when users enter the password from the lock screen, the system is not unloacking and the session is not being restore. its just looping and stuck on the lockscreen. 
##lockout duration must be 5 mins, its locking too quickily before 5 minutes. some times the locking warning appears during the time when the user is using the system 

5. My Profile:
## Redesign the my profile page to be more minimalistic there is too much blank white space. Check attached screenshot (.github/BUGS/my_profile.png), Personal Information and Change Password must be side by side. 
## change buttons to red and maintain visual consistency with other modules. 
## for all users, use the default picture as frontend/public/images/user.jpg and it must be consistent in the database. User will change the profile pictures as they desire. 

6. Dashboard Footer Adjustment: 
##Adjust the height of the logout button section on the sidebar to match the same height as the footer section of all the dashboards. Check the attached screenshot (.github/BUGS/dashboard_footer.png). This must be adjusted for all dashboards and user roles. 

7. Sidebar Menu Adjustments:
##The current sidebar menu views are not prioritizing the menu items view, some of the menu items i hidden and users have to scroll to view and see all the menus. Check attached screenshots for all user roles: (".github/BUGS/admin_staff_sidebar.png" and ".github/BUGS/customer_sidebar.png")
##On the admin/staff sidebar menus: move the sidebar expand/collapse icon to top next to the company name. Utilize the blank space between menu items and the logout button to display more menu items. Reduce the height of the logout button to match the height of the dashboard footer. 
##On the customer sidebar menu: move the sidebar expand/collapse icon to top next to the company name. Utilize the blank space between menu items and the logout button to display more menu items. Reduce the height of the logout button to match the height of the dashboard footer. Remove the back to shop button or place it just bove the logout button. 

======================================
======================================

#ADMIN ISSUES:


======================================
======================================

#STAFF ISSUES:


======================================
======================================

#CUSTOMER ISSUES:

1. On successfull login, customers must be redirected to their dashboard not to the shop. If they want to shop they can click on any links back to shop. 

2. Session persistance issues:
## when customer logs in and opens the shop, when they try to access their dashboard, they are asked to login again and they are automatically redirected to the "/login?session_expired=true" but they will be currecntly logged in. This happening for all user roles

3. Customer Dashboard:
## on Recent Orders: when user clicks View Order button, it redirects to "/customer/orders/?" but the Order detail page is showing hardcoded data, it must fetch real dynamic data from the database about the order details. 
## on Recent Orders: when user clicks View All link, it redirects to "/customer/orders/" but the Orders page is showing "No Orders Yet" even though the orders are there, it must fetch real dynamic data from the database about the orders.
## the info card widgets on the dashboard are too big and some icons are not visible (check attached .github/BUGS/customer_infos.png): redisign them to be more minimalistic and redable, reduce their size they serve as informational and must not take too much space. 

4. My Orders:
## customer's My Orders module page (/customer/orders) is not fetching real dynamic data from the system database. It is showing "No Orders Yet" even though the customer has orders. 

5. My Addresses:
## customer's My Addresses module page (/customer/addresses) is not fetching real dynamic data from the system database. It is showing "No Orders Yet" even though the customer has addresses.

6. My Wishlist (/customer/wishlist):
## customer's My wishlist module page (/customer/wishlist) is not fetching real dynamic data from the system database. It is showing "Your Wishlist is Empty and Sample Product $XX.XX / kg" even though the customer has clicked on some products to be added to wishlist. (check attached .github/BUGS/wishlist.png)
## the wishlist icons on the products are not adding the products to the wish list, fix the whole wishlist workflow so that customers can CRUD own wishlist

7. Support Tickets (/customer/support):
##Add CRUD action buttons for customer tickets, they can soft delete/cancel any ticket from their end and mark the ticket as deleted/cancelled by user. 





======================================