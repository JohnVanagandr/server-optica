import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';

const WidgetApp = ({ config, products }) => {
    const [viewer, setViewer] = useState({ isOpen: false, url: '', title: '' });

    const openViewer = (url, title) => setViewer({ isOpen: true, url, title });
    const closeViewer = () => setViewer({ isOpen: false, url: '', title: '' });

    const whatsappLink = config.whatsapp_number 
        ? `https://wa.me/${config.whatsapp_number.replace(/\D/g, '')}?text=Hola,%20vengo%20del%20catálogo%20virtual,%20estoy%20interesado%20en%20sus%20productos.`
        : null;

    return (
        <div 
            className="min-h-screen font-sans antialiased text-gray-800 relative pb-24"
            style={{ backgroundColor: config.background_color || '#f4f4f5' }}
        >
            {/* CABECERA TOP */}
            <header 
                className="flex items-center px-4 py-4 md:px-6 shadow-sm rounded-b-lg md:rounded-none"
                style={{ backgroundColor: config.primary_color || '#16a34a', color: '#ffffff' }}
            >
                <div className="w-8 h-8 rounded-full border border-white/40 flex items-center justify-center mr-3 bg-white/10">
                    {/* Icono Lucide: LayoutGrid */}
                    <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24">
                        <rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>
                    </svg>
                </div>
                <h1 className="text-lg md:text-xl font-bold tracking-tight truncate">
                    {config.title || 'Catálogo de Monturas'}
                </h1>
            </header>

            <main className="p-4 md:p-6 max-w-7xl mx-auto">
                {/* MENSAJE DE BIENVENIDA */}
                {config.welcome_message && (
                    <div className="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm">
                        <p className="text-gray-600 text-sm md:text-base">
                            {config.welcome_message}
                        </p>
                    </div>
                )}

                {/* GRILLA DE PRODUCTOS */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                    {products.map(product => (
                        <div key={product.id} className="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 flex flex-col overflow-hidden group">
                            
                            {/* CONTENEDOR DE IMAGEN (Sin Padding) */}
                            <div className="relative aspect-[4/3] bg-gray-50 flex items-center justify-center overflow-hidden">
                                <img 
                                    src={product.image_url} 
                                    alt={product.name} 
                                    /* Se eliminó el padding del contenedor para que la imagen llegue hasta los bordes */
                                    className="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-500" 
                                />
                                
                                {/* BOTONES FLOTANTES DE ACCIÓN */}
                                <div className="absolute right-3 top-3 flex flex-col gap-2 z-10">
                                    
                                    {/* Botón 360° */}
                                    {product.ar_model_url && (
                                        <button 
                                            onClick={() => openViewer(product.ar_model_url, `Vista 360° - ${product.name}`)}
                                            className="cursor-pointer w-9 h-9 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-full flex items-center justify-center shadow-[0_2px_8px_rgba(0,0,0,0.08)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.15)] hover:scale-110 hover:border-gray-300 text-gray-600 hover:text-black transition-all duration-300"
                                            title="Ver 360°"
                                        >
                                            {/* Icono Lucide: RotateCcw (Rotación 360) */}
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24">
                                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
                                            </svg>
                                        </button>
                                    )}

                                    {/* Botón Probador (Face Tracking) */}
                                    {product.web_tracking_url && (
                                        <button 
                                            onClick={() => openViewer(product.web_tracking_url, `Probador Virtual - ${product.name}`)}
                                            className="cursor-pointer w-9 h-9 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-full flex items-center justify-center shadow-[0_2px_8px_rgba(0,0,0,0.08)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.15)] hover:scale-110 hover:border-gray-300 text-gray-600 hover:text-black transition-all duration-300"
                                            title="Probador Virtual"
                                        >
                                            {/* Icono Lucide: ScanFace (Reconocimiento Facial) */}
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24">
                                                <path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01"/><path d="M15 9h.01"/>
                                            </svg>
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* INFORMACIÓN DEL PRODUCTO */}
                            <div className="p-4 flex flex-col flex-grow justify-between border-t border-gray-50 bg-white">
                                <h3 className="text-sm font-medium text-gray-800 line-clamp-2 mb-3" title={product.name}>
                                    {product.name}
                                </h3>
                                
                                {/* Precio alineado a la izquierda, sin carrito a la derecha */}
                                <div className="mt-auto">
                                    <span className="font-bold text-base" style={{ color: config.primary_color || '#16a34a' }}>
                                        ${Number(product.base_price).toLocaleString('es-CO')}
                                    </span>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </main>

            {/* BOTÓN FLOTANTE DE WHATSAPP (Lucide Icon: MessageCircle) */}
            {whatsappLink && (
                <a 
                    href={whatsappLink}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="cursor-pointer fixed bottom-6 right-6 z-40 w-14 h-14 bg-[#25D366] hover:bg-[#1ebd5a] rounded-full shadow-lg flex items-center justify-center text-white transition-transform hover:scale-110"
                    title="Contactar por WhatsApp"
                >
                    <svg className="w-7 h-7" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24">
                        <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>
                    </svg>
                </a>
            )}

            {/* MODAL INMERSIVO (Visor de Unity) */}
            {viewer.isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-2 md:p-6 animate-in fade-in duration-200">
                    <div className="bg-white w-full max-w-5xl h-[90vh] md:h-full rounded-2xl md:rounded-3xl shadow-2xl flex flex-col overflow-hidden relative">
                        <div className="flex justify-between items-center px-6 py-4 border-b border-gray-100 bg-white">
                            <h2 className="font-bold text-gray-800 text-lg">{viewer.title}</h2>
                            <button onClick={closeViewer} className="cursor-pointer p-2 bg-gray-100 hover:bg-gray-200 hover:text-red-600 rounded-full transition-colors">
                                {/* Icono Lucide: X */}
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" viewBox="0 0 24 24">
                                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div className="flex-grow bg-gray-900 relative">
                            <iframe src={viewer.url} className="absolute inset-0 w-full h-full border-0" allow="camera; microphone; autoplay; fullscreen" title="Visor 3D"></iframe>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

const rootElement = document.getElementById('widget-react-root');
if (rootElement) {
    const root = createRoot(rootElement);
    const props = window.WIDGET_PROPS || { config: {}, products: [] };
    root.render(<WidgetApp {...props} />);
}