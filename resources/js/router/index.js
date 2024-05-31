import { createApp } from "vue";
import store from "~/store";
// import Meta from "vue-meta";
import { createMetaManager } from 'vue-meta'
import { createRouter, createWebHistory, useRoute } from "vue-router";
import routes from "./routes";
import App from "~/components/App";
import VueMatchMedia from '@webqam/vue-match-media'
import Snackbar from 'vuejs-snackbar'


console.log('INDEX');

const app = createApp(App);

// console.log('ROUTE', useRoute());

app.use(store);
app.use(createMetaManager);
app.component('snackbar', Snackbar)

// The middleware for every page of the application.
const globalMiddleware = ["check-auth"];

// Load middleware modules dynamically.
const routeMiddleware = resolveMiddleware(
  require.context("~/middleware", false, /.*\.js$/),
);

const router = makeRouter();


const breakpoints = {
  xs: '360px',
  sm: '410px',
  md: '768px',
  lg: '992px',
  xl: '1280px',
  xxl: '1366px'
}



app.use(VueMatchMedia, { breakpoints: breakpoints })

app.use(router);

app.mount('#app');

export default router;

/**
 * Create a new router instance.
 *
 * @return {Router}
 */
function makeRouter() {
  const router = createRouter({
    history: createWebHistory(),
    routes,
  });

  router.beforeEach(beforeEach);
  // router.afterEach(afterEach);

  return router;
}

/**
 * Resolve components loading functional.
 *
 * @param  {Array} components - An array of component to resolve.
 * @return {Array} - An array of resolved components with page loading.
 */
router.getMatchedComponents = (to) => {
  // Extract the path from the route object
  const { path } = to;
  // Initialize an array to store the matched components
  let matchedComponents = [];

  // Iterate through the routes to find the matching component
  routes.forEach(route => {
    if (route.path === path) {
      // If it matches, push the corresponding component to the array with function 'import page'
      matchedComponents.push(page(route.component));
    } else if (route.children) {
      // Iterate through the children routes to find the matching component
      route.children.forEach(childRoute => {
        if (childRoute.path === path) {
          // If children matches, push the corresponding component to the array with function 'import page'
          matchedComponents.push(page(childRoute.component));
        }
      });
    }
  });
  
  // Return the array of matched components with functional of import page
  return matchedComponents;
}

/**
 * Global router guard.
 *
 * @param {Route} to
 * @param {Route} from
 * @param {Function} next
 */
async function beforeEach(to, from, next) {
  let components = [];
  console.log('Navigating from', from, 'to', to.path);
  try {
    console.log('trying resolve', typeof router.getMatchedComponents({ ...to }));
    // Get the matched components and resolve them.

    components = await resolveComponents(
      router.getMatchedComponents({ ...to }),
    );
    // components = await resolveComponents(routes);
    console.log('Success resolve');
  } catch (error) {
    if (/^Loading( CSS)? chunk (\d)+ failed\./.test(error.message)) {
      window.location.reload(true);
      return;
    }
  }

  if (components.length === 0) {
    return next();
  }

  console.log('components in beforeEach: ', components[0]);

  // Start the loading bar.
  // if (components[components.length - 1].loading !== false) {
  //   router.app.$nextTick(() => router.app.$loading.start())
  // }

  // Get the middleware for all the matched components.
  const middleware = getMiddleware(components);

  // Call each middleware.
  callMiddleware(middleware, to, from, (...args) => {
    // Set the application layout only if "next()" was called with no args.
    if (args.length === 0) {
      router.app.setLayout(components[0].layout || "");
    }

    next(...args);
  });
}

/**
 * Global after hook.
 *
 * @param {Route} to
 * @param {Route} from
 * We will use here our fetch user logic
 * @param {Function} next
 */
async function afterEach(to, from, next) {
  await router.app.$nextTick();
  return next();
  // router.app.$loading.finish()
}

/**
 * Call each middleware.
 *
 * @param {Array} middleware
 * @param {Route} to
 * @param {Route} from
 * @param {Function} next
 */
function callMiddleware(middleware, to, from, next) {
  const stack = middleware.reverse();

  const _next = (...args) => {
    // Stop if "_next" was called with an argument or the stack is empty.
    if (args.length > 0 || stack.length === 0) {
      // if (args.length > 0) {
      //   router.app.$loading.finish()
      // }

      return next(...args);
    }

    const middleware = stack.pop();

    if (typeof middleware === "function") {
      middleware(to, from, _next);
    } else if (routeMiddleware[middleware]) {
      routeMiddleware[middleware](to, from, _next);
    } else {
      throw Error(`Undefined middleware [${middleware}]`);
    }
  };

  _next();
}

/**
 * Resolve async components.
 *
 * @param  {Array} components
 * @return {Array}
 */
function resolveComponents(components) {
  // console.log('Component: ', components);
  return Promise.all(
    components.map((component) => {
      return typeof component === "function" ? component() : component;
    }),
  );
}

/**
 * Merge the the global middleware with the components middleware.
 *
 * @param  {Array} components
 * @return {Array}
 */
function getMiddleware(components) {
  const middleware = [...globalMiddleware];

  components
    .filter((c) => c.middleware)
    .forEach((component) => {
      if (Array.isArray(component.middleware)) {
        middleware.push(...component.middleware);
      } else {
        middleware.push(component.middleware);
      }
    });

  return middleware;
}

/**
 * Scroll Behavior
 *
 * @link https://router.vuejs.org/en/advanced/scroll-behavior.html
 *
 * @param  {Route} to
 * @param  {Route} from
 * @param  {Object|undefined} savedPosition
 * @return {Object}
 */
function scrollBehavior(to, from, savedPosition) {
  if (savedPosition) {
    return savedPosition;
  }

  if (to.hash) {
    return { selector: to.hash };
  }

  const [component] = router.getMatchedComponents({ ...to }).slice(-1);

  if (component && component.scrollToTop === false) {
    return {};
  }

  // return {}
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      resolve({ x: 0, y: 0 });
    }, 300);
  });
}

/**
 * @param  {Object} requireContext
 * @return {Object}
 */
function resolveMiddleware(requireContext) {
  return requireContext
    .keys()
    .map((file) => [file.replace(/(^.\/)|(\.js$)/g, ""), requireContext(file)])
    .reduce(
      (guards, [name, guard]) => ({ ...guards, [name]: guard.default }),
      {},
    );
}
