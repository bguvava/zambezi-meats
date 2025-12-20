ISSUES FOUND THAT NEED TO BE FIXED:
# Required color palette: CF0D0F, F6211F, EFEFEF, 6F6F6F, 4D4B4C. This system must load as fast as possible and minimalistic, it must be secure, robust, reliable, fully responsive and viewable on all screen sizes.

1. Admin Dashboard: 
- when loaded the dashboard is showing these errors: "Error loading dashboard. Failed to load dashboard data"
- the browser console shows these logs: 
auth.js:70  GET http://localhost:8000/api/v1/auth/user 401 (Unauthorized)
dispatchXhrRequest @ axios.js?v=19c42782:1984
xhr @ axios.js?v=19c42782:1894
dispatchRequest @ axios.js?v=19c42782:2363
Promise.then
_request @ axios.js?v=19c42782:2557
request @ axios.js?v=19c42782:2486
Axios$1.<computed> @ axios.js?v=19c42782:2594
wrap @ axios.js?v=19c42782:13
fetchUser @ auth.js:70
initialize @ auth.js:50
await in initialize
wrappedAction @ pinia.js?v=19c42782:4795
store.<computed> @ pinia.js?v=19c42782:4482
(anonymous) @ main.js:39
DashboardPage.vue:59 Failed to fetch dashboard data: TypeError: Cannot read properties of undefined (reading 'totalRevenue')
    at fetchDashboardData (DashboardPage.vue:34:23)
    at async DashboardPage.vue:20:3
fetchDashboardData @ DashboardPage.vue:59
await in fetchDashboardData
(anonymous) @ DashboardPage.vue:20
(anonymous) @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:3603
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
hook.__weh.hook.__weh @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:3592
flushPostFlushCbs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1985
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:2013
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
- all the modules still have no contents

2. Staff Dashboard: 
- when loaded the dashboard is showing these errors: "Error loading dashboard. Failed to load dashboard data"
- the browser console shows these logs: 
DashboardPage.vue:37 Failed to fetch dashboard data: TypeError: Cannot read properties of undefined (reading 'stats')
    at fetchDashboardData (DashboardPage.vue:32:35)
    at async DashboardPage.vue:22:3
fetchDashboardData @ DashboardPage.vue:37
await in fetchDashboardData
(anonymous) @ DashboardPage.vue:22
(anonymous) @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:3603
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
hook.__weh.hook.__weh @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:3592
flushPostFlushCbs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1985
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:2013
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
- all the modules still have no contents


3. When customer presses checkout button, only a blank white screen is displayed and nothing happens. 
- the browser console logs show this:
CartPanel.vue:55 [Vue warn]: Unhandled error during execution of render function 
  at <OrderSummary> 
  at <CheckoutPage onVnodeUnmounted=fn<onVnodeUnmounted> ref=Ref< null > > 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1716
logError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1903
handleError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1897
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:4634
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
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5286
processFragment @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5370
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5180
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
handleCheckout @ CartPanel.vue:55
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:7497
CartPanel.vue:55 [Vue warn]: Unhandled error during execution of component update 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1716
logError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1903
handleError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1897
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1855
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
handleCheckout @ CartPanel.vue:55
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:7497
CartPanel.vue:55 [Vue Router warn]: uncaught error during route navigation:
warn$1 @ vue-router.js?v=19c42782:168
triggerError @ vue-router.js?v=19c42782:2687
(anonymous) @ vue-router.js?v=19c42782:2711
Promise.catch
handleScroll @ vue-router.js?v=19c42782:2711
finalizeNavigation @ vue-router.js?v=19c42782:2631
(anonymous) @ vue-router.js?v=19c42782:2558
Promise.then
pushWithRedirect @ vue-router.js?v=19c42782:2546
push @ vue-router.js?v=19c42782:2499
handleCheckout @ CartPanel.vue:55
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:7497
CartPanel.vue:55 TypeError: Cannot read properties of undefined (reading 'toFixed')
    at OrderSummary.vue:53:50
    at renderList (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:3665:59)
    at Proxy._sfc_render (OrderSummary.vue:61:13)
    at renderComponentRoot (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:4617:37)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5444:41)
    at ReactiveEffect.run (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:409:16)
    at setupRenderEffect (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5513:3)
    at mountComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5399:10)
    at processComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5379:8)
    at patch (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5183:28)
triggerError @ vue-router.js?v=19c42782:2688
(anonymous) @ vue-router.js?v=19c42782:2711
Promise.catch
handleScroll @ vue-router.js?v=19c42782:2711
finalizeNavigation @ vue-router.js?v=19c42782:2631
(anonymous) @ vue-router.js?v=19c42782:2558
Promise.then
pushWithRedirect @ vue-router.js?v=19c42782:2546
push @ vue-router.js?v=19c42782:2499
handleCheckout @ CartPanel.vue:55
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:7497
OrderSummary.vue:53 Uncaught (in promise) TypeError: Cannot read properties of undefined (reading 'toFixed')
    at OrderSummary.vue:53:50
    at renderList (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:3665:59)
    at Proxy._sfc_render (OrderSummary.vue:61:13)
    at renderComponentRoot (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:4617:37)
    at ReactiveEffect.componentUpdateFn [as fn] (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5444:41)
    at ReactiveEffect.run (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:409:16)
    at setupRenderEffect (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5513:3)
    at mountComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5399:10)
    at processComponent (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5379:8)
    at patch (vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5183:28)
(anonymous) @ OrderSummary.vue:53
renderList @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:3665
_sfc_render @ OrderSummary.vue:61
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:4617
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
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5286
mountElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5253
processElement @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5236
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5182
mountChildren @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5286
processFragment @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5370
patch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:5180
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
handleCheckout @ CartPanel.vue:55
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=19c42782:7497

4. when the system logs out the user after in activity, it must show a modal that notifies the user they have been logged out. The logout time after inactivity is 5minutes. Users must login again. 

5. When users clear cart and comes another users and logs out the selected items in the cart are still appearing dispite being cleared before. fix the cart keeping the items from a previous customer. 

6. The cart is disabling the checkout when the total is less than $100.00, the 

7. Add multicurrency where users can choose between USD$ or AU$ based or their preference and the system must autocalculate the equivalence based on the chosen currency. 

8. Remove the "Categories" menu from the navigations and all its submenus. Users can view available dynamic categories from the main shop page. 

9. Fix the social media icons on the contact us section on home page to match the official ones on the footer section. 
