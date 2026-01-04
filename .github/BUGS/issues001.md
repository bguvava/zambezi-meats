#ISSUES FOUND THAT NEED TO BE FIXED:

---
- Required color palette: CF0D0F, F6211F, EFEFEF, 6F6F6F, 4D4B4C. Offcial logos are in ".github/" folder (official_logo.png, official_logo-white.png, official_logo_landscape.png, official_logo_landscape_white.png), choose the best based on the placement - the logo files to the appropriate folder.
- THIS SYSTEM MUST BE FAST, SECURE, ROBUST, AND OPERATE 99.9% UPTIME WITH 0.000% ERRORS. HANDLE PAYMENTS SECURELY ALL THE TIME. 
---

#1. Fast loading and startup time:
- Store all images and assets in the codebase, and preload them to make sure that the application loads as fast as possible

---

#2. Logo usage and placement:
- where there are dark backgrounds use the white logo, where there are light backgrounds use red/dark Logo
- on homepage and navigation menu, before scrolling use the white logo ".github/official_logo_landscape_white.png" and when scrolled down and when navigation changes use this logo ".github/official_logo_landscape.png".  Remove the title next to the logo
- on the footer use this logo instead ".github/official_logo_landscape_white.png" and remove the title next to the logo.
- on the authentication screens (login, forgot password, and create account) use this logo "official_logo.png" 
- on the dashboards use this logo "official_logo-white.png"

---

#3. Refresh and logout bug:
- for all users the moment they refresh the browser they are logged out of the system. The session must stay persistent even if the user refreshes the browser. The user session is only supposed to be logged out after 5 minutes of inactivity or after clicking logout.
- on load load all frontend shop pages (home, shop, about, contact) are showing this browser console logs:
auth.js:70  GET http://localhost:8000/api/v1/auth/user 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=87907051:1984
xhr @ axios.js?v=87907051:1894
dispatchRequest @ axios.js?v=87907051:2363
Promise.then
_request @ axios.js?v=87907051:2557
request @ axios.js?v=87907051:2486
Axios$1.<computed> @ axios.js?v=87907051:2594
wrap @ axios.js?v=87907051:13
fetchUser @ auth.js:70
initialize @ auth.js:50
await in initialize
wrappedAction @ pinia.js?v=87907051:4795
store.<computed> @ pinia.js?v=87907051:4482
(anonymous) @ main.js:51

---

#4. Contact Us Messages:
- there are 2 contact us places on this application (contact us at home page and contact us page), remove the contact us section on home page and keep the one on contact us page only.
- add honeypot on the contact us page and section to filter and limit bots.
- Capture all subscription emails and store them in the database . Also capture requests to unsbscribe
- The contact us messages must be captured and stored in a database
- create a Messages module for admin and staff to view all messages recieved on their dashboards. Update the sidebar menu to add this module. It must have 2 tabs for viewiwng messages recieved and another tab for all subscriptions. Add option to view/delete messages
- add social media icons on the contact us page, add colors on the sections

---

#5. Admin/Staff dashboard routing from shop:
- both admin and staff roles when they login, they are correctly redirected to their actual dashboards. The is an issue when they click on a view shop from their actual dashboard, they are redirected to the shop. The bug is: from this point if the user clicks on the user dropdown menu from the shop - they are redirected to the customer dashboard not their actual role based dashboards. 
- When users refresh from their main dashboards they are logged out automatically. 

---

#6. Logout and Inactivity Timeout:
- the required time for automatic logout after inactivity is 5 mins. The system must show a lock screen with the current user and must confirm password to continue with the session or completely logout.  Provide user friendly message about the inactivity. 
- on automatic logout due to inactivity, users are redirected to this: http://localhost:5174/?message=Session+expired+due+to+inactivity, provide a clear message or add an additional lock screen that give user options to logout or continue with the session rather than automatically kick out the user from the system. 
- when users click dismiss from the popup message, the session continues briefly then completely logs out, but when a different user tries to login they get this url "http://localhost:5174/login?session_expired=true" and they can login no matter what unless its the previous user. 
---

#7. Checkout Process:
- the checkout process is failing, when user is logged in and clicks the checkout button they are redirected to "http://localhost:5174/checkout" but the page is blank white with nothing
- the browser console logs show these:
ProductCard.vue:192 The specified value "NaN" cannot be parsed, or is out of range.
beforeUpdate @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:8080
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invokeDirectiveHook @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2269
patchElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5298
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5241
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
patchBlockChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5340
patchElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5307
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5241
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
patchBlockChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5340
patchElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5307
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5241
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5492
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1327
set @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1356
_createElementVNode.onClick._cache.<computed>._cache.<computed> @ ProductCard.vue:192
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
CartPage.vue:52 [Vue warn]: Unhandled error during execution of render function 
  at <OrderSummary> 
  at <CheckoutPage onVnodeUnmounted=fn<onVnodeUnmounted> ref=Ref< null > > 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1716
logError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1903
handleError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1897
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:4634
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5444
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5183
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
processFragment @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5370
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5180
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5447
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5183
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5492
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1327
finalizeNavigation @ vue-router.js?v=87907051:2630
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPage.vue:52
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
CartPage.vue:52 [Vue warn]: Unhandled error during execution of component update 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1716
logError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1903
handleError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1897
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1855
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1327
finalizeNavigation @ vue-router.js?v=87907051:2630
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPage.vue:52
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
CartPage.vue:52 [Vue Router warn]: uncaught error during route navigation:
warn$1 @ vue-router.js?v=87907051:168
triggerError @ vue-router.js?v=87907051:2687
(anonymous) @ vue-router.js?v=87907051:2711
Promise.catch
handleScroll @ vue-router.js?v=87907051:2711
finalizeNavigation @ vue-router.js?v=87907051:2631
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPage.vue:52
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
CartPage.vue:52 TypeError: imageSrc.startsWith is not a function
    at Proxy.getProductImage (OrderSummary.vue:49:18)
    at OrderSummary.vue:83:22
    at renderList (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:3665:59)
    at Proxy._sfc_render (OrderSummary.vue:104:13)
    at renderComponentRoot (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:4617:37)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5444:41)
    at ReactiveEffect.run (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409:16)
    at setupRenderEffect (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513:3)
    at mountComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399:10)
    at processComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379:8)
triggerError @ vue-router.js?v=87907051:2688
(anonymous) @ vue-router.js?v=87907051:2711
Promise.catch
handleScroll @ vue-router.js?v=87907051:2711
finalizeNavigation @ vue-router.js?v=87907051:2631
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPage.vue:52
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
OrderSummary.vue:49 Uncaught (in promise) TypeError: imageSrc.startsWith is not a function
    at Proxy.getProductImage (OrderSummary.vue:49:18)
    at OrderSummary.vue:83:22
    at renderList (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:3665:59)
    at Proxy._sfc_render (OrderSummary.vue:104:13)
    at renderComponentRoot (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:4617:37)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5444:41)
    at ReactiveEffect.run (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409:16)
    at setupRenderEffect (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513:3)
    at mountComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399:10)
    at processComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379:8)
getProductImage @ OrderSummary.vue:49
(anonymous) @ OrderSummary.vue:83
renderList @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:3665
_sfc_render @ OrderSummary.vue:104
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:4617
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5444
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5183
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
processFragment @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5370
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5180
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5447
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5183
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5492
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1327
finalizeNavigation @ vue-router.js?v=87907051:2630
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPage.vue:52
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497


- and when user clicks back from the blank white checkout page, the shop also turns in to blank white screen with the following messages:

CartPanel.vue:62 [Vue warn]: Unhandled error during execution of render function 
  at <OrderSummary> 
  at <CheckoutPage onVnodeUnmounted=fn<onVnodeUnmounted> ref=Ref< null > > 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1716
logError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1903
handleError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1897
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:4634
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5444
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5183
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
processFragment @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5370
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5180
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5447
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5183
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5492
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1327
finalizeNavigation @ vue-router.js?v=87907051:2630
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPanel.vue:62
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
CartPanel.vue:62 [Vue warn]: Unhandled error during execution of component update 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1716
logError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1903
handleError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1897
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1855
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1327
finalizeNavigation @ vue-router.js?v=87907051:2630
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPanel.vue:62
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
CartPanel.vue:62 [Vue Router warn]: uncaught error during route navigation:
warn$1 @ vue-router.js?v=87907051:168
triggerError @ vue-router.js?v=87907051:2687
(anonymous) @ vue-router.js?v=87907051:2711
Promise.catch
handleScroll @ vue-router.js?v=87907051:2711
finalizeNavigation @ vue-router.js?v=87907051:2631
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPanel.vue:62
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
CartPanel.vue:62 TypeError: imageSrc.startsWith is not a function
    at Proxy.getProductImage (OrderSummary.vue:49:18)
    at OrderSummary.vue:83:22
    at renderList (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:3665:59)
    at Proxy._sfc_render (OrderSummary.vue:104:13)
    at renderComponentRoot (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:4617:37)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5444:41)
    at ReactiveEffect.run (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409:16)
    at setupRenderEffect (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513:3)
    at mountComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399:10)
    at processComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379:8)
triggerError @ vue-router.js?v=87907051:2688
(anonymous) @ vue-router.js?v=87907051:2711
Promise.catch
handleScroll @ vue-router.js?v=87907051:2711
finalizeNavigation @ vue-router.js?v=87907051:2631
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPanel.vue:62
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
OrderSummary.vue:49 Uncaught (in promise) TypeError: imageSrc.startsWith is not a function
    at Proxy.getProductImage (OrderSummary.vue:49:18)
    at OrderSummary.vue:83:22
    at renderList (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:3665:59)
    at Proxy._sfc_render (OrderSummary.vue:104:13)
    at renderComponentRoot (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:4617:37)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5444:41)
    at ReactiveEffect.run (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409:16)
    at setupRenderEffect (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513:3)
    at mountComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399:10)
    at processComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379:8)
getProductImage @ OrderSummary.vue:49
(anonymous) @ OrderSummary.vue:83
renderList @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:3665
_sfc_render @ OrderSummary.vue:104
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:4617
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5444
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5183
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
processFragment @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5370
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5180
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5182
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5447
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
setupRenderEffect @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5513
mountComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5399
processComponent @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5379
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5183
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5492
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1327
finalizeNavigation @ vue-router.js?v=87907051:2630
(anonymous) @ vue-router.js?v=87907051:2558
Promise.then
pushWithRedirect @ vue-router.js?v=87907051:2546
push @ vue-router.js?v=87907051:2499
handleCheckout @ CartPanel.vue:62
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7497
vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1716 [Vue warn]: Unhandled error during execution of component update 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1716
logError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1903
handleError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1897
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1855
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1327
finalizeNavigation @ vue-router.js?v=87907051:2630
(anonymous) @ vue-router.js?v=87907051:2662
Promise.then
(anonymous) @ vue-router.js?v=87907051:2661
(anonymous) @ vue-router.js?v=87907051:1321
popStateHandler @ vue-router.js?v=87907051:1320
vue-router.js?v=87907051:168 [Vue Router warn]: uncaught error during route navigation:
warn$1 @ vue-router.js?v=87907051:168
triggerError @ vue-router.js?v=87907051:2687
(anonymous) @ vue-router.js?v=87907051:2711
Promise.catch
handleScroll @ vue-router.js?v=87907051:2711
finalizeNavigation @ vue-router.js?v=87907051:2631
(anonymous) @ vue-router.js?v=87907051:2662
Promise.then
(anonymous) @ vue-router.js?v=87907051:2661
(anonymous) @ vue-router.js?v=87907051:1321
popStateHandler @ vue-router.js?v=87907051:1320
vue-router.js?v=87907051:2688 TypeError: Cannot read properties of null (reading 'parentNode')
    at parentNode (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7008:29)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5492:31)
    at ReactiveEffect.run (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409:16)
    at ReactiveEffect.runIfDirty (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:436:27)
    at callWithErrorHandling (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853:31)
    at flushJobs (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002:5)
triggerError @ vue-router.js?v=87907051:2688
(anonymous) @ vue-router.js?v=87907051:2711
Promise.catch
handleScroll @ vue-router.js?v=87907051:2711
finalizeNavigation @ vue-router.js?v=87907051:2631
(anonymous) @ vue-router.js?v=87907051:2662
Promise.then
(anonymous) @ vue-router.js?v=87907051:2661
(anonymous) @ vue-router.js?v=87907051:1321
popStateHandler @ vue-router.js?v=87907051:1320
vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7008 Uncaught (in promise) TypeError: Cannot read properties of null (reading 'parentNode')
    at parentNode (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7008:29)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5492:31)
    at ReactiveEffect.run (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409:16)
    at ReactiveEffect.runIfDirty (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:436:27)
    at callWithErrorHandling (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853:31)
    at flushJobs (vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002:5)
parentNode @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:7008
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5492
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=87907051:1327
finalizeNavigation @ vue-router.js?v=87907051:2630
(anonymous) @ vue-router.js?v=87907051:2662
Promise.then
(anonymous) @ vue-router.js?v=87907051:2661
(anonymous) @ vue-router.js?v=87907051:1321
popStateHandler @ vue-router.js?v=87907051:1320


---

#8. My Profile unresponsive:
- the my profile is not editable for all user roles. User can't edit or manage their profiles. Fix the My Profile module for all user roles to be fully functional. Ensure profile picture upload and editing all fields and they must persist in the database. 
- Add this my profile module to all users including admin and staff roles with proper routing

---

#9. Sidebar navigation:
- make sure the sidebar navigation is responsive and adapts to different scree sizes. Some menu items are hidden. 
-  reduce the size of the icons and text and make the more minimalistic
- ensure the sidebar menu is scrollable and does not hide some menu items on smaller screen sizes
---

#10. Notification System:
- the notification bell on the top bar is not responsive 
- ensure the whole notification process if fully functional and fetching correct and full notification and viewable via the notification bell

---

