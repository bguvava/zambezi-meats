======================================
#ISSUES FOUND THAT NEED TO BE FIXED:
======================================
- Required color palette: #CF0D0F, #F6211F, #EFEFEF, #6F6F6F, #4D4B4C. Official logos are in the ".github/" folder (official_logo.png, official_logo-white.png, official_logo_landscape.png, official_logo_landscape_white.png), choose the best based on the placement - the logo files to the appropriate folder.
- THIS SYSTEM MUST BE FAST, SECURE, ROBUST, AND OPERATE 99.9% UPTIME WITH 0.000% ERRORS. HANDLE PAYMENTS SECURELY ALL THE TIME. 
======================================

#GLOBAL ISSUES:
1.  Homepage:
## the featured products section on the homepage, takes too long to load and the products only load only when the homepage is loaded again from another page. 
## the featured products are being shown with json data format check the attache screenshot ".github/BUGS/screenshots/featured.png". this is the example of the data being displayed: { "id": 17, "name": "Breast", "slug": "chicken-breast", "description": "Chicken breast fillets", "image_url": null, "icon": null, "sort_order": 2, "is_active": true, "created_at": "2026-01-03T14:53:26+00:00", "updated_at": "2026-01-03T14:53:26+00:00" }


2. Session persistance issues:
## on broswer refresh from the dashboard (all roles), the user is automatically logged out and redirected to the login page. the session must stay persistant. everytime any user role refreshes from the dashboard they are directed to the login screen, even though their session is still alive.

3. Session persistance issues:
## on broswer refresh from the dashboard (all roles), the user is automatically redirected to the login page. the session will be still on but then redirects to the login. the session must stay persistant. everytime any user role refreshes from the dashboard they are directed to the login screen, even though their session is still alive. 

======================================
======================================

#ADMIN ISSUES:
1. Products:
##Edit product: when admin updates a product, the button shows saving but the edits/updates are not saved into the database, when user opens again the view product it will be containing old data. 
#product image upload is also not staying consistent, when user upload its not commiting to the database. 
##Add product is not working and completing, when admin enters all information on add product modal, they get these browser console logs:  
----
dashboard.js:414  POST http://localhost:8000/api/v1/admin/products 422 (Unprocessable Content)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
httpMethod @ axios.js?v=c8a3afb6:2608
wrap @ axios.js?v=c8a3afb6:13
createProduct @ dashboard.js:414
createProduct @ adminProducts.js:119
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
saveProduct @ ProductsPage.vue:202
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
adminProducts.js:128 Failed to create product: {message: 'Validation failed.', errors: {…}, status: 422}
createProduct @ adminProducts.js:128
await in createProduct
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
saveProduct @ ProductsPage.vue:202
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
ProductsPage.vue:208 Failed to save product: {message: 'Validation failed.', errors: {…}, status: 422}
saveProduct @ ProductsPage.vue:208
await in saveProduct
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
----

2. Deliveries:
##When the user opens the deliveries module, it shows these browser console errors:
----
adminDelivery.js:595 Failed to fetch staff list: TypeError: Cannot read properties of undefined (reading 'data')
    at Proxy.fetchStaffList (adminDelivery.js:593:40)
    at async loadStaffList (DeliveryPage.vue:136:3)
fetchStaffList @ adminDelivery.js:595
await in fetchStaffList
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
loadStaffList @ DeliveryPage.vue:136
(anonymous) @ DeliveryPage.vue:367
(anonymous) @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:3603
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
hook.__weh.hook.__weh @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:3592
flushPostFlushCbs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1985
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:2013
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1327
finalizeNavigation @ vue-router.js?v=c8a3afb6:2630
(anonymous) @ vue-router.js?v=c8a3afb6:2558
Promise.then
pushWithRedirect @ vue-router.js?v=c8a3afb6:2546
push @ vue-router.js?v=c8a3afb6:2499
navigate @ vue-router.js?v=c8a3afb6:2185
(anonymous) @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7516
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1868
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
----

3. Dashboard:
##the Profit vs Expenses (Monthly) chart on the dashboard is not fecthing real dynamic data, it is showing static and hardcoded figures/numbers

4. Category:
##Add Category modal: admin enters all the details, and the image but then all the data is not comtting and being saved to the database. The browser console are showing these error messages:
----
dashboard.js:491  POST http://localhost:8000/api/v1/admin/categories 422 (Unprocessable Content)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
httpMethod @ axios.js?v=c8a3afb6:2608
wrap @ axios.js?v=c8a3afb6:13
createCategory @ dashboard.js:491
createCategory @ adminCategories.js:57
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
saveCategory @ CategoriesPage.vue:153
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
adminCategories.js:65 Failed to create category: {message: 'Validation failed.', errors: {…}, status: 422}
createCategory @ adminCategories.js:65
await in createCategory
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
saveCategory @ CategoriesPage.vue:153
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
CategoriesPage.vue:159 Failed to save category: {message: 'Validation failed.', errors: {…}, status: 422}
saveCategory @ CategoriesPage.vue:159
await in saveCategory
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497

----

5. Orders:
##View Orders: the view order details modal window is not showing customer details specifically on these: name (showing guest), email, phone number. 

6. Reports & Analytics:
#on the Reports & Analytics module, keep the Revenue Trend chart but other types of reports that we want to have on these:
--> Sales Summary Reports
--> Products vs Orders Reports
--> Financial Summary Reports
--> Top Products vs Low Performing reports
##Make sure the reports have the same format and can be exported to the pdf, follow this format: [header section with logo+company name | heading section with report title | report body section with the reports contents | footer section with copyright statement, generated by who, date/time]
##keep the Top Products and Top Customers sections 

7. System Settings:
##the system settings module contains a lot of settings that are not useful, minimize the number of settings to the ones that are strictly used accordingly.
##these settings must be consistent and stored into the database, and they must effectively control and manage the whole application system.  

8. Invoices Management:
##during the ordering and payment process, the system must generate invoces for easier tracking of fincances. Add a CRUD invoice system to the admin role and only view/search on the staff side. Add corresponding menus and invoice modules. 
##On the payment process, add an invoice either before or after completing the payment Process.  
##Add these invoices to the to the database and mark them according to their status. Add a corresponding invoice management module to the staff/admin dashboard with corresponding menu items on the sidebars menu.  

9. Session persistance issues:
## on broswer refresh from the dashboard (all roles), the user is automatically redirected to the login page. the session will be still on but then redirects to the login. the session must stay persistant. everytime any user role refreshes from the dashboard they are directed to the login screen, even though their session is still alive.

======================================
======================================

#STAFF ISSUES:

1. Session persistance issues:
## on broswer refresh from the dashboard (all roles), the user is automatically redirected to the login page. the session will be still on but then redirects to the login. the session must stay persistant. everytime any user role refreshes from the dashboard they are directed to the login screen, even though their session is still alive.

======================================
======================================

#CUSTOMER ISSUES:

1. My Orders:
##When customer click view orders, they are rediceted to the My Orders module page (/customer/orders/?) is not fetching real dynamic data. It is showing this error: "Error loading order. Failed to load order details" 

2. My Addresses:
##When adding/editng the addresses, nothing is displayed  (just a blank grey screen) the modals are not being displayed.

3. Footer Placement:
#Adjust the placement of the dashboard footer to the fixed bottom of the window (check attached screenshot .github/BUGS/screenshots/addresses.png)
##The same issue is happening on the my wishlist page view

4. Cart & Payment Process: 
##On the payment process, add an invoice either before or after completing the payment Process. 
##Add these invoices to the to the database and mark them according to their status. Add a corresponding invoice management module to the staff/admin dashboard with corresponding menu items on the sidebars menu. 
##the payment process is failing to complete its getting stuck along the way, scan the whole payment process and ensure it completes 

5. Session persistance issues:
## on broswer refresh from the dashboard (all roles), the user is automatically redirected to the login page. the session will be still on but then redirects to the login. the session must stay persistant. everytime any user role refreshes from the dashboard they are directed to the login screen, even though their session is still alive.




======================================