import { Link } from '@inertiajs/react';

export default function Index({ categories }) {
  return (
    <div
      style={{
        padding: 20,
        backgroundColor: '#f9fafb',
        minHeight: '100vh',
        fontFamily: 'Arial, sans-serif'
      }}
    >
      <h1
        style={{
          fontSize: '28px',
          fontWeight: 'bold',
          color: '#2563eb',
          marginBottom: '20px'
        }}
      >
        Lista de Categorías (Prueba)
      </h1>

      {/* Botón para crear nueva categoría */}
      <Link
        href="/categories/create"
        style={{
          display: 'inline-block',
          marginBottom: '20px',
          padding: '10px 15px',
          backgroundColor: '#16a34a',
          color: 'white',
          borderRadius: '6px',
          textDecoration: 'none'
        }}
      >
        + Nueva Categoría
      </Link>

      {/* Mostrar lista o mensaje vacío */}
      {categories.length === 0 ? (
        <p style={{ color: '#555' }}>No hay categorías aún.</p>
      ) : (
        <table
          style={{
            width: '100%',
            borderCollapse: 'collapse',
            backgroundColor: 'white',
            boxShadow: '0 2px 6px rgba(0,0,0,0.1)'
          }}
        >
          <thead>
            <tr
              style={{
                backgroundColor: '#2563eb',
                color: 'white',
                textAlign: 'left'
              }}
            >
              <th style={{ padding: '10px' }}>#</th>
              <th style={{ padding: '10px' }}>Nombre</th>
              <th style={{ padding: '10px' }}>Descripción</th>
            </tr>
          </thead>
          <tbody>
            {categories.map((cat, index) => (
              <tr
                key={cat.id}
                style={{
                  borderBottom: '1px solid #ddd',
                  backgroundColor: index % 2 === 0 ? '#f9fafb' : 'white'
                }}
              >
                <td style={{ padding: '10px' }}>{cat.id}</td>
                <td style={{ padding: '10px', fontWeight: 'bold' }}>
                  {cat.nombre}
                </td>
                <td style={{ padding: '10px' }}>{cat.descripcion}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
