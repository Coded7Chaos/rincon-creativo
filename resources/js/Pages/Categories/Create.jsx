import { useForm, Link } from '@inertiajs/react';

export default function Create() {
  const { data, setData, post, processing, errors } = useForm({
    nombre: '',
    descripcion: ''
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    post('/categories');
  };

  return (
    <div style={{ padding: 20 }}>
      <h1 style={{ fontSize: '24px', fontWeight: 'bold', color: '#2563eb' }}>
        Crear nueva categoría
      </h1>

      <form onSubmit={handleSubmit} style={{ marginTop: 20 }}>
        <div style={{ marginBottom: 15 }}>
          <label>Nombre:</label>
          <input
            type="text"
            value={data.nombre}
            onChange={(e) => setData('nombre', e.target.value)}
            style={{
              display: 'block',
              width: '100%',
              padding: 8,
              borderRadius: 6,
              border: '1px solid #ccc'
            }}
          />
          {errors.nombre && (
            <div style={{ color: 'red' }}>{errors.nombre}</div>
          )}
        </div>

        <div style={{ marginBottom: 15 }}>
          <label>Descripción:</label>
          <textarea
            value={data.descripcion}
            onChange={(e) => setData('descripcion', e.target.value)}
            style={{
              display: 'block',
              width: '100%',
              padding: 8,
              borderRadius: 6,
              border: '1px solid #ccc'
            }}
          />
          {errors.descripcion && (
            <div style={{ color: 'red' }}>{errors.descripcion}</div>
          )}
        </div>

        <button
          type="submit"
          disabled={processing}
          style={{
            backgroundColor: '#2563eb',
            color: 'white',
            padding: '10px 15px',
            border: 'none',
            borderRadius: 6,
            cursor: 'pointer'
          }}
        >
          Guardar
        </button>

        <Link
          href="/categories"
          style={{
            marginLeft: 10,
            color: '#2563eb',
            textDecoration: 'underline'
          }}
        >
          Volver
        </Link>
      </form>
    </div>
  );
}
