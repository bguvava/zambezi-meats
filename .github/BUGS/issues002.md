ISSUES FOUND THAT NEED TO BE FIXED:
# Required color palette: CF0D0F, F6211F, EFEFEF, 6F6F6F, 4D4B4C
# This system must load as fast as possible and minimalistic, it must be secure, robust, reliable, fully responsive and viewable on all screen sizes.

1. Official social media handles for Zambezi Meats are as follows, add only these 3 handles and remove twitter and youtube on the footer section:
- https://www.facebook.com/share/17hrbEMpKY/
- https://www.instagram.com/zambezi_meats?igsh=OXZkNjVvb2w2enll
- https://www.tiktok.com/@zambezi.meats?_r=1&_t=ZS-92Jw9xNcw8O

2. On the cart view, add a button to open the cart in a full page view so that customers can view all their items properly

3. Create Account failing:
- when users create account and enter all the information, this error is shown: "Registration failed"
- the browser console is showing these logs:
auth.js:62  GET http://localhost:8000/api/v1/auth/user 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=19c42782:1984
xhr @ axios.js?v=19c42782:1894
dispatchRequest @ axios.js?v=19c42782:2363
Promise.then
_request @ axios.js?v=19c42782:2557
request @ axios.js?v=19c42782:2486
Axios$1.<computed> @ axios.js?v=19c42782:2594
wrap @ axios.js?v=19c42782:13
fetchUser @ auth.js:62
initialize @ auth.js:47
await in initialize
wrappedAction @ pinia.js?v=19c42782:4795
store.<computed> @ pinia.js?v=19c42782:4482
(anonymous) @ main.js:39
auth.js:50 Auth initialization failed: AxiosError$1 {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
initialize @ auth.js:50
await in initialize
wrappedAction @ pinia.js?v=19c42782:4795
store.<computed> @ pinia.js?v=19c42782:4482
(anonymous) @ main.js:39
auth.js:109  POST http://localhost:8000/api/v1/auth/register 422 (Unprocessable Content)
dispatchXhrRequest @ axios.js?v=19c42782:1984
xhr @ axios.js?v=19c42782:1894
dispatchRequest @ axios.js?v=19c42782:2363
Promise.then
_request @ axios.js?v=19c42782:2557
request @ axios.js?v=19c42782:2486
httpMethod @ axios.js?v=19c42782:2608
wrap @ axios.js?v=19c42782:13
register @ auth.js:109
await in register
wrappedAction @ pinia.js?v=19c42782:4795
store.<computed> @ pinia.js?v=19c42782:4482
register @ useAuth.js:50
handleSubmit @ RegisterPage.vue:192
(anonymous) @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:8266
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:7497

4. Login Page Errors:
- when login page loads, it shows these browser console logs:
auth.js:62  GET http://localhost:8000/api/v1/auth/user 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=19c42782:1984
xhr @ axios.js?v=19c42782:1894
dispatchRequest @ axios.js?v=19c42782:2363
Promise.then
_request @ axios.js?v=19c42782:2557
request @ axios.js?v=19c42782:2486
Axios$1.<computed> @ axios.js?v=19c42782:2594
wrap @ axios.js?v=19c42782:13
fetchUser @ auth.js:62
initialize @ auth.js:47
await in initialize
wrappedAction @ pinia.js?v=19c42782:4795
store.<computed> @ pinia.js?v=19c42782:4482
(anonymous) @ main.js:39
auth.js:50 Auth initialization failed: AxiosError$1 {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
initialize @ auth.js:50
await in initialize
wrappedAction @ pinia.js?v=19c42782:4795
store.<computed> @ pinia.js?v=19c42782:4482
(anonymous) @ main.js:39

- after logging in these errors are shown on the browser console:
index.js:1 content loaded
index.59b2ae62.js:3 Content Script: Initializing
useAuth.js:38 [Vue warn]: Extraneous non-props attributes (visible) were passed to component but could not be automatically inherited because component renders fragment or text or teleport root nodes. 
  at <SessionWarningModal key=0 visible=false onExtend=fn  ... > 
  at <GuestLayout onVnodeUnmounted=fn<onVnodeUnmounted> ref=Ref< null > > 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1716
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:4657
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5444
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5183
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5182
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5447
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5183
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5492
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1327
finalizeNavigation @ vue-router.js?v=19c42782:2630
(anonymous) @ vue-router.js?v=19c42782:2558
Promise.then
pushWithRedirect @ vue-router.js?v=19c42782:2546
push @ vue-router.js?v=19c42782:2499
login @ useAuth.js:38
await in login
handleSubmit @ LoginPage.vue:97
(anonymous) @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:8266
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:7497
useAuth.js:38 [Vue warn]: Extraneous non-emits event listeners (extend, logout) were passed to component but could not be automatically inherited because component renders fragment or text root nodes. If the listener is intended to be a component custom event listener only, declare it using the "emits" option. 
  at <SessionWarningModal key=0 visible=false onExtend=fn  ... > 
  at <GuestLayout onVnodeUnmounted=fn<onVnodeUnmounted> ref=Ref< null > > 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1716
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:4658
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5444
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5183
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5182
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5447
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5183
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5492
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1327
finalizeNavigation @ vue-router.js?v=19c42782:2630
(anonymous) @ vue-router.js?v=19c42782:2558
Promise.then
pushWithRedirect @ vue-router.js?v=19c42782:2546
push @ vue-router.js?v=19c42782:2499
login @ useAuth.js:38
await in login
handleSubmit @ LoginPage.vue:97
(anonymous) @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:8266
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:7497

5. Shopping and Cart errors:
- when shopping, if user clicks + to add kgs/weight of any product before adding the item to the cart - the kg numbers dissapear
- when the product is added to the cart, the weight will be written as "NaNkg" and the total shows as "$NaN" and the products page shows "NaNkg in cart"
- when customer clicks the product from the cart list, they get a 404 not found page. 


6. Favico: use this favico "frontend/public/images/favico.ico"

7. Home page load errors: on home page load, the browser console shows these browser console errors: 
auth.js:62  GET http://localhost:8000/api/v1/auth/user 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=19c42782:1984
xhr @ axios.js?v=19c42782:1894
dispatchRequest @ axios.js?v=19c42782:2363
Promise.then
_request @ axios.js?v=19c42782:2557
request @ axios.js?v=19c42782:2486
Axios$1.<computed> @ axios.js?v=19c42782:2594
wrap @ axios.js?v=19c42782:13
fetchUser @ auth.js:62
initialize @ auth.js:47
await in initialize
wrappedAction @ pinia.js?v=19c42782:4795
store.<computed> @ pinia.js?v=19c42782:4482
(anonymous) @ main.js:39
auth.js:50 Auth initialization failed: AxiosError$1 {message: 'Request failed with status code 401', name: 'AxiosError', code: 'ERR_BAD_REQUEST', config: {…}, request: XMLHttpRequest, …}
initialize @ auth.js:50
await in initialize
wrappedAction @ pinia.js?v=19c42782:4795
store.<computed> @ pinia.js?v=19c42782:4482
(anonymous) @ main.js:39

8. Checkout: when customer clicks the checkout, they are shown the login screen even though they are already logged in, they must proceed to the payment page instead if they are already logged in. If not logged in they must first login if they have the account. if they do not have an account they must:
i) either create an account and return or be redirected to the checkout page, secure the checkout process and make sure the users are authenticated on paying. 
ii) implement the best way to accomodate once off buyers who do not want to create an account, they must fully provide their details for account/customer identification and delivery purposes
- the main payment platform for this project is Stripe (with Paypal, Visa, Mastercard, Afterpay), Afterpay is the commonly used payment method. 

9. when customers login and they click on user dropdown menu - the My dashboard menu leads to a 404 not found page instead of the customer dashboard. Fix the whole authentication flow, from account creation, logging in, all menus must respond correctly, and the cart and checkout must proceed accordingly when user is logged in. Secure the checkout process. 

10. Admin dashboard panel: we have developed and implemented all the admin modules that are on the admin sidebar navigation menu, but when admin clicks to access them, all of them have nothing on them, this is what they show:
- Dashboard module (/admin) shows this only: Info  card widgets and  Welcome to the admin dashboard. Manage your store from here.
- Users module (/admin/users) shows this only: Manage user accounts, roles, and permissions.
- Products module (/admin/products) shows this only: Add, edit, and manage your product catalog.
- Categories module (/admin/categories) shows this only: Create and manage product categories and subcategories.
- Orders module (/admin/orders) shows this only: View and manage all customer orders.
- Inventory module (/admin/inventory) shows this only: Track and manage product inventory levels.
- Reports module (/admin/reports) shows this only: View sales reports and business analytics.
- Settings module (/admin/settings) shows this only: Configure store settings and system preferences.

11. Staff Dashboard panel: we have developed and implemented all the staff modules that are on the admin sidebar navigation menu, but when admin clicks to access them, all of them have nothing on them, this is what they show:
- Dashboard (/staff): shows static data, must fetch real dynamic data from the database for that specific staff
- Orders (/staff/orders): shows static data, must fetch real dynamic data from the database for that specific staff
- My Deliveries (/staff/deliveries): : shows static data, must fetch real dynamic data from the database for that specific staff

12. Customer Dashboard Panel: we have developed and implemented all the staff modules that are on the admin sidebar navigation menu, but when admin clicks to access them, all of them have nothing on them, this is what they show:
- Dashboard () must fetch real dynamic data from the database for that specific customer
- Orders module (/customer/orders): shows static data, must fetch real dynamic data from the database for that specific customer
- My Profile (/customer/profile) : shows static data, must fetch real dynamic data from the database for that specific customer
- My Addresses(/customer/addresses): shows static data, must fetch real dynamic data from the database for that specific customer
- My Wishlist (/customer/wishlist): shows static data, must fetch real dynamic data from the database for that specific customer

13. When all users click on logout, they must automatically be redirected to the homepage with a fresh and clean session (clear all sessions and cookies automatically)














