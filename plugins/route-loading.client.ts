export default defineNuxtPlugin(() => {
  const routeLoading = useState('app-route-loading', () => false)
  const router = useRouter()

  router.beforeEach(() => {
    routeLoading.value = true
  })

  router.afterEach(() => {
    nextTick(() => {
      routeLoading.value = false
    })
  })
})
