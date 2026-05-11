import React, { useState } from 'react'
import { Button, Drawer, Table } from 'antd'
import type { ColumnsType } from 'antd/es/table'

type Row = {
  id: number
  product: string
  state: string
  city: string
}

const columns: ColumnsType<Row> = [
  { title: 'ID', dataIndex: 'id', key: 'id', width: 72 },
  { title: 'Product', dataIndex: 'product', key: 'product' },
  { title: 'State', dataIndex: 'state', key: 'state' },
  { title: 'City', dataIndex: 'city', key: 'city' }
]

const dummyRows: Row[] = [
  { id: 1, product: 'moto', state: 'Bihar', city: 'Patna' },
  { id: 2, product: 'demo', state: 'Delhi', city: 'New Delhi' },
  { id: 3, product: 'sample', state: 'Gujarat', city: 'Ahmedabad' },
  { id: 4, product: 'trial', state: 'Karnataka', city: 'Bengaluru' }
]

/**
 * Opens a drawer with an Ant Design **Table** (dummy rows). Mounted beside the **City** field.
 */
export function AyraDrawerGridButton(): React.JSX.Element {
  const [open, setOpen] = useState(false)

  return (
    <>
      <Button size='small' type='default' onClick={ () => setOpen(true) }>
        Ayra grid
      </Button>
      <Drawer
        title='Ayra — sample grid'
        placement='right'
        width={ 720 }
        open={ open }
        onClose={ () => setOpen(false) }
        destroyOnClose
      >
        <Table<Row>
          size='small'
          rowKey='id'
          pagination={ false }
          columns={ columns }
          dataSource={ dummyRows }
        />
      </Drawer>
    </>
  )
}
