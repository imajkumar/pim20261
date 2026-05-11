import { configureStore } from '@reduxjs/toolkit'
import { indiaGeoApi } from './indiaGeoApi'

export const indiaGeoStore = configureStore({
  reducer: {
    [indiaGeoApi.reducerPath]: indiaGeoApi.reducer
  },
  middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(indiaGeoApi.middleware)
})
