import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react'

export type SelectOptionItem = {
  key: string
  value: string
}

export type CitySelectOptionsResponse = {
  totalItems: number
  items: SelectOptionItem[]
}

export const indiaGeoApi = createApi({
  reducerPath: 'indiaGeoApi',
  baseQuery: fetchBaseQuery({
    baseUrl: '',
    credentials: 'same-origin',
    prepareHeaders: (headers) => {
      headers.set('Accept', 'application/json')
      headers.set('Content-Type', 'application/json')
      return headers
    }
  }),
  tagTypes: ['CityOptions'],
  endpoints: (build) => ({
    cityOptionsForState: build.query<CitySelectOptionsResponse, { objectId: number; stateSlug: string }>({
      query: ({ objectId, stateSlug }) => ({
        url: '/pimcore-studio/api/data-objects/select-options',
        method: 'POST',
        body: {
          objectId,
          fieldName: 'city',
          changedData: { state: stateSlug },
          context: {}
        }
      }),
      providesTags: (_result, _err, arg) => [{ type: 'CityOptions', id: `${arg.objectId}:${arg.stateSlug}` }]
    })
  })
})

export const { useLazyCityOptionsForStateQuery } = indiaGeoApi
