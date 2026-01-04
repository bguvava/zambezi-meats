======================================
#ISSUES FOUND THAT NEED TO BE FIXED:
======================================
- Required color palette: #CF0D0F, #F6211F, #EFEFEF, #6F6F6F, #4D4B4C. Official logos are in the ".github/" folder (official_logo.png, official_logo-white.png, official_logo_landscape.png, official_logo_landscape_white.png), choose the best based on the placement - the logo files to the appropriate folder.
- THIS SYSTEM MUST BE FAST, SECURE, ROBUST, AND OPERATE 99.9% UPTIME WITH 0.000% ERRORS. HANDLE PAYMENTS SECURELY ALL THE TIME. 
======================================

#GLOBAL ISSUES:
1. Homepage:
# on the Why Zambezi Meats: The Difference is Quality section: redesign it to just icons 
# move this section to the about us page
# add a timeless promotional section that attracts new customers and improve on new leads and customer retention. be creative as an experienced sales marketer. 

2. Blog:
#add a SEO optimized blog with a just 3 timelss articles that will assist in drawing and getting external links/clicks to come to this website
#add a menu item link to the blog. 



======================================
======================================

#ADMIN ISSUES:
1. Dashboard:
#the logo on the sidebar menu is invisible, use the white version/turn it into white so that it is visible. See screenshot ".github/BUGS/screenshots/admin_sidebar_expanded.png"
#the sidebar menu is not scrollable other menu items are not visible when the menu is expanded. it must be scrollable up and down to view all menu items. See screenshot ".github/BUGS/screenshots/admin_sidebar_collapsed.png" and ".github/BUGS/screenshots/admin_sidebar_expanded.png"
#onload the dashboard shows this error: Error Loading Dashboard. Failed to load dashboard data" and the browser console logs are showing:
----
admin:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/dashboard' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
DashboardPage.vue:138 Failed to fetch dashboard data: {message: 'Network error. Please check your connection.', status: 0}
fetchDashboardData @ DashboardPage.vue:138
await in fetchDashboardData
(anonymous) @ DashboardPage.vue:122
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:357  GET http://localhost:8000/api/v1/admin/dashboard net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getOverview @ dashboard.js:357
fetchDashboardData @ DashboardPage.vue:132
(anonymous) @ DashboardPage.vue:122
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
admin:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/dashboard' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
DashboardPage.vue:138 Failed to fetch dashboard data: {message: 'Network error. Please check your connection.', status: 0}
fetchDashboardData @ DashboardPage.vue:138
await in fetchDashboardData
(anonymous) @ DashboardPage.vue:148
setInterval
startPolling @ DashboardPage.vue:147
(anonymous) @ DashboardPage.vue:123
await in (anonymous)
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:357  GET http://localhost:8000/api/v1/admin/dashboard net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getOverview @ dashboard.js:357
fetchDashboardData @ DashboardPage.vue:132
(anonymous) @ DashboardPage.vue:148
setInterval
startPolling @ DashboardPage.vue:147
(anonymous) @ DashboardPage.vue:123
await in (anonymous)
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
admin:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/dashboard' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
DashboardPage.vue:138 Failed to fetch dashboard data: {message: 'Network error. Please check your connection.', status: 0}
fetchDashboardData @ DashboardPage.vue:138
await in fetchDashboardData
(anonymous) @ DashboardPage.vue:148
setInterval
startPolling @ DashboardPage.vue:147
(anonymous) @ DashboardPage.vue:123
await in (anonymous)
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:357  GET http://localhost:8000/api/v1/admin/dashboard net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getOverview @ dashboard.js:357
fetchDashboardData @ DashboardPage.vue:132
(anonymous) @ DashboardPage.vue:148
setInterval
startPolling @ DashboardPage.vue:147
(anonymous) @ DashboardPage.vue:123
await in (anonymous)
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
admin:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/dashboard' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
DashboardPage.vue:138 Failed to fetch dashboard data: {message: 'Network error. Please check your connection.', status: 0}
fetchDashboardData @ DashboardPage.vue:138
await in fetchDashboardData
(anonymous) @ DashboardPage.vue:148
setInterval
startPolling @ DashboardPage.vue:147
(anonymous) @ DashboardPage.vue:123
await in (anonymous)
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:357  GET http://localhost:8000/api/v1/admin/dashboard net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getOverview @ dashboard.js:357
fetchDashboardData @ DashboardPage.vue:132
(anonymous) @ DashboardPage.vue:148
setInterval
startPolling @ DashboardPage.vue:147
(anonymous) @ DashboardPage.vue:123
await in (anonymous)
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28

----

2. Products:
#on load it shows this error: "Error loading products. Network error. Please check your connection." The browser console shows these logs:
----
products:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/products?page=1&per_page=15' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
adminProducts.js:86 Failed to fetch products: {message: 'Network error. Please check your connection.', status: 0}
fetchProducts @ adminProducts.js:86
await in fetchProducts
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchProducts @ ProductsPage.vue:112
(anonymous) @ ProductsPage.vue:94
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:424  GET http://localhost:8000/api/v1/admin/products?page=1&per_page=15 net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getProducts @ dashboard.js:424
fetchProducts @ adminProducts.js:74
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchProducts @ ProductsPage.vue:112
(anonymous) @ ProductsPage.vue:94
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
products:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/categories' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
adminCategories.js:45 Failed to fetch categories: {message: 'Network error. Please check your connection.', status: 0}
fetchCategories @ adminCategories.js:45
await in fetchCategories
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
(anonymous) @ ProductsPage.vue:95
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:509  GET http://localhost:8000/api/v1/admin/categories net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getCategories @ dashboard.js:509
fetchCategories @ adminCategories.js:39
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
(anonymous) @ ProductsPage.vue:95
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28

----

3. Categories:
#on load it shows this error: "Error loading categories. Network error. Please check your connection." The browser console shows these logs:
----
categories:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/categories' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
adminCategories.js:45 Failed to fetch categories: {message: 'Network error. Please check your connection.', status: 0}
fetchCategories @ adminCategories.js:45
await in fetchCategories
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchCategories @ CategoriesPage.vue:88
(anonymous) @ CategoriesPage.vue:82
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:509  GET http://localhost:8000/api/v1/admin/categories net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getCategories @ dashboard.js:509
fetchCategories @ adminCategories.js:39
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchCategories @ CategoriesPage.vue:88
(anonymous) @ CategoriesPage.vue:82
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28

----

4. Orders:
#on load it shows this error: "Error loading orders. Network error. Please check your connection." The browser console shows these logs:

----
orders:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/orders?page=1&per_page=15' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
adminOrders.js:101 Failed to fetch orders: {message: 'Network error. Please check your connection.', status: 0}
fetchOrders @ adminOrders.js:101
await in fetchOrders
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchOrders @ OrdersPage.vue:154
(anonymous) @ OrdersPage.vue:118
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:367  GET http://localhost:8000/api/v1/admin/orders?page=1&per_page=15 net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getOrders @ dashboard.js:367
fetchOrders @ adminOrders.js:89
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchOrders @ OrdersPage.vue:154
(anonymous) @ OrdersPage.vue:118
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28

----
5. Invoices:
#on load it shows this error: "Failed to load invoices" The browser console shows these logs:
----
invoices:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/invoices?page=1&per_page=15' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
InvoicesPage.vue:317 Error loading invoices: {message: 'Network error. Please check your connection.', status: 0}
loadInvoices @ InvoicesPage.vue:317
await in loadInvoices
(anonymous) @ InvoicesPage.vue:387
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:1287  GET http://localhost:8000/api/v1/admin/invoices?page=1&per_page=15 net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getInvoices @ dashboard.js:1287
loadInvoices @ InvoicesPage.vue:309
(anonymous) @ InvoicesPage.vue:387
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
invoices:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/invoices/stats' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
InvoicesPage.vue:330 Error loading stats: {message: 'Network error. Please check your connection.', status: 0}
loadStats @ InvoicesPage.vue:330
await in loadStats
(anonymous) @ InvoicesPage.vue:388
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:1311  GET http://localhost:8000/api/v1/admin/invoices/stats net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getInvoiceStats @ dashboard.js:1311
loadStats @ InvoicesPage.vue:325
(anonymous) @ InvoicesPage.vue:388
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28

----
6. Inventory Management
#shows these browser console logs:
----
inventory:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/categories' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
adminCategories.js:45 Failed to fetch categories: {message: 'Network error. Please check your connection.', status: 0}
fetchCategories @ adminCategories.js:45
await in fetchCategories
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
(anonymous) @ InventoryPage.vue:138
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:509  GET http://localhost:8000/api/v1/admin/categories net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getCategories @ dashboard.js:509
fetchCategories @ adminCategories.js:39
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
(anonymous) @ InventoryPage.vue:138
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28

----
7. Delivery Management
#shows these browser console logs:
----
deliveries:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/staff?role=staff&status=active' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
adminDelivery.js:595 Failed to fetch staff list: {message: 'Network error. Please check your connection.', status: 0}
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
dashboard.js:577  GET http://localhost:8000/api/v1/admin/staff?role=staff&status=active net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getStaff @ dashboard.js:577
fetchStaffList @ adminDelivery.js:589
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
deliveries:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/staff?role=staff&status=active' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
adminDelivery.js:595 Failed to fetch staff list: {message: 'Network error. Please check your connection.', status: 0}
fetchStaffList @ adminDelivery.js:595
await in fetchStaffList
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
loadStaffList @ DeliveryPage.vue:136
(anonymous) @ DeliveryPage.vue:353
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
baseWatchOptions.call @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4418
job @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1633
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1940
baseWatchOptions.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4427
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1642
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1327
set @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1356
onClick @ DeliveryPage.vue:405
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
dashboard.js:577  GET http://localhost:8000/api/v1/admin/staff?role=staff&status=active net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getStaff @ dashboard.js:577
fetchStaffList @ adminDelivery.js:589
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
loadStaffList @ DeliveryPage.vue:136
(anonymous) @ DeliveryPage.vue:353
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
baseWatchOptions.call @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4418
job @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1633
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1940
baseWatchOptions.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4427
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1642
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1327
set @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1356
onClick @ DeliveryPage.vue:405
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
deliveries:1 Access to XMLHttpRequest at 'http://localhost:8000/api/v1/admin/staff?role=staff&status=active' from origin 'http://localhost:5174' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
adminDelivery.js:595 Failed to fetch staff list: {message: 'Network error. Please check your connection.', status: 0}
fetchStaffList @ adminDelivery.js:595
await in fetchStaffList
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
loadStaffList @ DeliveryPage.vue:136
(anonymous) @ DeliveryPage.vue:353
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
baseWatchOptions.call @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4418
job @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1633
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1940
baseWatchOptions.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4427
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1642
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1327
set @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1356
onClick @ DeliveryPage.vue:405
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
dashboard.js:577  GET http://localhost:8000/api/v1/admin/staff?role=staff&status=active net::ERR_FAILED 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getStaff @ dashboard.js:577
fetchStaffList @ adminDelivery.js:589
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
loadStaffList @ DeliveryPage.vue:136
(anonymous) @ DeliveryPage.vue:353
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
baseWatchOptions.call @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4418
job @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1633
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1940
baseWatchOptions.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4427
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1642
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1327
set @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1356
onClick @ DeliveryPage.vue:405
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497

----

8. Messages:
#messages and subsscriptions is working fine, we want to add the tickets/help tab because there is no access to it on the admin/staff dashboards. Customers are loggin in tickets and help messages but no admin/staff can view them. Add respective full CRUD access for admin and staff 
#add a Tickets/helpdsk tab on this messages module with full action buttons all fully functional


9. Reports & Analytics
#Remove all other reports and their buttons and remain with only 3 decisive reports that will help management to make key decisions. 
#keep the Revenue Trend chart and Top Products+Top Customers sections
#the reports are just too many, management need to make financial and key business decisions using reports generated from this system, but only keep 3 major reports that cover key business and financial aspects of this system. 
#for pdf export maintain the same format as other pdf in the system eg the users pdf format, but add official logo on the header. 


10.Settings
# the settings fields are too many again they need to be trimmed and removed and only remain with the key and main settings that really control and manage the SYSTEM
# when the settings are edited and saved, a sucess message pops up but the changes are not being submitted/stored to the databases, when user load the window old settings values are only shown
# remove these settings they are either duplicating or unnecessary: Delivery Configuration, Operating Hours, Social Media, SEO Configuration, Notifications, Feature Toggles, SMTP Configuration
#make sure these settings are fully controlling and managing the system: Store Information, Payments, Currency Configuration, Security Configuration, 



======================================
======================================

#STAFF ISSUES:

1. Order Queue
#on load it is showing this error: "Error loading orders" and the browser console showing these:
----
dashboard.js:129  GET http://localhost:8000/api/v1/staff/orders/queue?status=&search=&date=today&deliveryType=&page=1&per_page=15 500 (Internal Server Error)
dispatchXhrRequest @ axios.js?v=c8a3afb6:1984
xhr @ axios.js?v=c8a3afb6:1894
dispatchRequest @ axios.js?v=c8a3afb6:2363
Promise.then
_request @ axios.js?v=c8a3afb6:2557
request @ axios.js?v=c8a3afb6:2486
Axios$1.<computed> @ axios.js?v=c8a3afb6:2594
wrap @ axios.js?v=c8a3afb6:13
getOrderQueue @ dashboard.js:129
fetchOrders @ staffOrders.js:75
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchOrders @ OrdersPage.vue:102
(anonymous) @ OrdersPage.vue:86
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
manageOrders @ DashboardPage.vue:168
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
staffOrders.js:94 Error fetching order queue: {message: 'An unexpected error occurred. Please try again later.', status: 500}
fetchOrders @ staffOrders.js:94
await in fetchOrders
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchOrders @ OrdersPage.vue:102
(anonymous) @ OrdersPage.vue:86
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
manageOrders @ DashboardPage.vue:168
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497
OrdersPage.vue:104 Failed to fetch orders: {message: 'An unexpected error occurred. Please try again later.', status: 500}
fetchOrders @ OrdersPage.vue:104
await in fetchOrders
(anonymous) @ OrdersPage.vue:86
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
manageOrders @ DashboardPage.vue:168
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
callWithAsyncErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1860
invoker @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:7497

----

2. My Deliveries & Pickups (/staff/deliveries)
#on load it shows this: "Loading deliveries..." and the browser console shows this:
----
staffDeliveries.js:92 [Vue warn]: Unhandled error during execution of render function 
  at <DeliveriesPage onVnodeUnmounted=fn<onVnodeUnmounted> ref=Ref< Proxy(Object) {__v_skip: true} > > 
  at <RouterView> 
  at <StaffLayout onVnodeUnmounted=fn<onVnodeUnmounted> ref=Ref< Proxy(Object) {__v_skip: true} > > 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1716
logError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1903
handleError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1897
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4634
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:5487
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1327
fetchTodaysDeliveries @ staffDeliveries.js:92
await in fetchTodaysDeliveries
fetchAll @ staffDeliveries.js:131
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchAll @ DeliveriesPage.vue:93
(anonymous) @ DeliveriesPage.vue:87
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
staffDeliveries.js:92 [Vue warn]: Unhandled error during execution of component update 
  at <DeliveriesPage onVnodeUnmounted=fn<onVnodeUnmounted> ref=Ref< Proxy(Object) {__v_skip: true} > > 
  at <RouterView> 
  at <StaffLayout onVnodeUnmounted=fn<onVnodeUnmounted> ref=Ref< Proxy(Object) {__v_skip: true} > > 
  at <RouterView> 
  at <App>
warn$1 @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1716
logError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1903
handleError @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1897
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1855
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1327
fetchTodaysDeliveries @ staffDeliveries.js:92
await in fetchTodaysDeliveries
fetchAll @ staffDeliveries.js:131
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchAll @ DeliveriesPage.vue:93
(anonymous) @ DeliveriesPage.vue:87
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28
staffDeliveries.js:31 Uncaught (in promise) TypeError: deliveries.value.filter is not a function
    at ComputedRefImpl.fn (staffDeliveries.js:31:22)
    at refreshComputed (vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:532:28)
    at get value (vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1499:3)
    at ComputedRefImpl.fn (staffDeliveries.js:72:36)
    at refreshComputed (vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:532:28)
    at isDirty (vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:514:118)
    at refreshComputed (vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:523:97)
    at get value (vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1499:3)
    at unref (vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1346:28)
    at Object.get (vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1352:63)
(anonymous) @ staffDeliveries.js:31
refreshComputed @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:532
get value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1499
(anonymous) @ staffDeliveries.js:72
refreshComputed @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:532
isDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:514
refreshComputed @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:523
get value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1499
unref @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1346
get @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1352
_sfc_render @ DeliveriesPage.vue:311
renderComponentRoot @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4617
componentUpdateFn @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:5487
run @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:409
runIfDirty @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:436
callWithErrorHandling @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1853
flushJobs @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:2002
Promise.then
queueFlush @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1944
queueJob @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1940
effect$1.scheduler @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:5509
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:429
endBatch @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:479
notify @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:694
trigger @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:686
set value @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:1327
fetchTodaysDeliveries @ staffDeliveries.js:92
await in fetchTodaysDeliveries
fetchAll @ staffDeliveries.js:131
wrappedAction @ pinia.js?v=c8a3afb6:4795
store.<computed> @ pinia.js?v=c8a3afb6:4482
fetchAll @ DeliveriesPage.vue:93
(anonymous) @ DeliveriesPage.vue:87
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
install @ vue-router.js?v=c8a3afb6:2746
use @ vue.runtime.esm-bundler-d6NXi4lR.js?v=c8a3afb6:4268
(anonymous) @ main.js:28

----

3. Invoices:
#invoice detail view: change the Download PDF button color from blue to red
#Download PDF redirects to this link "/staff/invoices/?" but then it shows a 404 page not found error.


4. My Activity
#remove this activity log completely from the system. we do not need it. 


======================================
======================================

#CUSTOMER ISSUES:

1. 




======================================