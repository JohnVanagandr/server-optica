import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import { LayoutGrid, RotateCcw, ScanFace, X } from 'lucide-react'; // Quitamos MessageCircle

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
            style={{ backgroundColor: config.background_color || '#e5e7eb' }}
        >
            {/* CABECERA TOP */}
            <header 
                className="flex items-center px-4 py-4 md:px-6 shadow-sm rounded-b-lg md:rounded-none z-10 relative"
                style={{ backgroundColor: config.primary_color || '#1e3a8a', color: '#ffffff' }}
            >
                <div className="w-8 h-8 rounded-full border border-white/40 flex items-center justify-center mr-3 bg-white/10 shadow-inner">
                    <LayoutGrid className="w-4 h-4 text-white" strokeWidth={2.5} />
                </div>
                <h1 className="text-lg md:text-xl font-bold tracking-tight truncate drop-shadow-sm">
                    {config.title || 'Catálogo de Monturas'}
                </h1>
            </header>

            <main className="p-4 md:p-6 max-w-7xl mx-auto">
                {/* MENSAJE DE BIENVENIDA */}
                {config.welcome_message && (
                    <div className="bg-white border border-gray-200/80 rounded-xl p-4 md:p-5 mb-6 md:mb-8 shadow-sm">
                        <p className="text-gray-600 text-sm md:text-base leading-relaxed">
                            {config.welcome_message}
                        </p>
                    </div>
                )}

                {/* GRILLA DE PRODUCTOS (Sin Precio) */}
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 md:gap-6">
                    {products.map(product => (
                        <div 
                            key={product.id} 
                            className="group bg-white rounded-xl md:rounded-2xl border border-gray-200 shadow-sm hover:shadow-xl hover:border-gray-300 hover:-translate-y-1.5 transition-all duration-300 ease-out flex flex-col overflow-hidden cursor-default"
                        >
                            {/* CAJA DE IMAGEN */}
                            <div className="relative aspect-[4/3] p-4 md:p-6 flex items-center justify-center overflow-hidden bg-white">
                                <img 
                                    src={product.image_url} 
                                    alt={product.name} 
                                    className="w-full h-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform duration-500 ease-out" 
                                />
                                
                                {/* BOTONES FLOTANTES LATERALES (Siempre visibles en móvil, Hover en PC) */}
                                <div className="absolute right-2 md:right-3 top-2 md:top-3 flex flex-col gap-1.5 md:gap-2 transition-all duration-300 z-10 opacity-100 translate-x-0 md:opacity-0 md:translate-x-2 group-hover:opacity-100 group-hover:translate-x-0">
                                    
                                    {/* Botón 360° */}
                                    {product.ar_model_url && (
                                        <button 
                                            onClick={() => openViewer(product.ar_model_url, `Vista 360° - ${product.name}`)}
                                            className="cursor-pointer w-8 h-8 md:w-10 md:h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow hover:shadow-md hover:scale-110 text-gray-600 hover:text-black transition-all duration-200 active:scale-95"
                                            title="Ver 360°"
                                        >
                                            <RotateCcw className="w-3.5 h-3.5 md:w-4 md:h-4" strokeWidth={2.5} />
                                        </button>
                                    )}

                                    {/* Botón Probador */}
                                    {product.web_tracking_url && (
                                        <button 
                                            onClick={() => openViewer(product.web_tracking_url, `Probador Virtual - ${product.name}`)}
                                            className="cursor-pointer w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center shadow hover:shadow-md hover:scale-110 text-white transition-all duration-200 active:scale-95"
                                            style={{ backgroundColor: config.primary_color || '#1e3a8a' }}
                                            title="Probador Virtual"
                                        >
                                            <ScanFace className="w-3.5 h-3.5 md:w-4 md:h-4" strokeWidth={2.5} />
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* INFORMACIÓN DEL PRODUCTO (Solo Marca y Nombre) */}
                            <div className="px-3 md:px-5 pb-4 md:pb-6 pt-2 flex flex-col flex-grow bg-white z-10 border-t border-gray-50">
                                <span className="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                    {product.brand || 'Exclusivo'}
                                </span>
                                <h3 className="text-sm md:text-[15px] font-bold text-slate-800 leading-snug line-clamp-2" title={product.name}>
                                    {product.name}
                                </h3>
                            </div>
                        </div>
                    ))}
                </div>
            </main>

            {/* BOTÓN FLOTANTE DE WHATSAPP (Logo Oficial) */}
            {whatsappLink && (
                <a 
                    href={whatsappLink}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="cursor-pointer fixed bottom-6 right-6 z-40 w-12 h-12 md:w-14 md:h-14 bg-[#25D366] hover:bg-[#1ebd5a] rounded-full shadow-[0_8px_25px_rgba(37,211,102,0.4)] flex items-center justify-center text-white transition-all duration-300 hover:scale-110 hover:-translate-y-1"
                    title="Contactar por WhatsApp"
                >
                    <svg className="w-6 h-6 md:w-8 md:h-8 fill-current" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm.082 21.163c-1.585 0-3.11-.408-4.461-1.183l-4.915 1.288 1.312-4.793c-.851-1.396-1.3-3.003-1.3-4.664 0-4.947 4.025-8.973 8.972-8.973 4.949 0 8.974 4.026 8.974 8.973 0 4.948-4.025 8.972-8.972 8.972z"/>
                    </svg>
                </a>
            )}

            {/* MODAL INMERSIVO */}
            {viewer.isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-3 md:p-6 animate-in fade-in duration-300">
                    <div className="bg-white w-full max-w-5xl h-[90dvh] md:h-[85vh] rounded-2xl md:rounded-3xl shadow-2xl flex flex-col overflow-hidden relative transform transition-all scale-100 animate-in zoom-in-95 duration-300">
                        <div className="flex justify-between items-center px-4 md:px-6 py-3 md:py-4 border-b border-gray-100 bg-white z-10 shrink-0">
                            <h2 className="font-bold text-gray-800 text-base md:text-lg tracking-tight truncate pr-4">{viewer.title}</h2>
                            <button onClick={closeViewer} className="cursor-pointer p-2 bg-gray-50 hover:bg-red-50 hover:text-red-600 rounded-full transition-colors shrink-0">
                                <X className="w-4 h-4 md:w-5 md:h-5" strokeWidth={2.5} />
                            </button>
                        </div>
                        <div className="flex-grow bg-gray-50 relative w-full h-full">
                            <iframe 
                                src={viewer.url} 
                                className="absolute inset-0 w-full h-full border-0" 
                                allow="camera; microphone; autoplay; fullscreen" 
                                title="Visor 3D"
                            ></iframe>
                        </div>
                        <div className="border-t border-gray-100 bg-white px-4 md:px-6 py-3 shrink-0 flex justify-end">
                            <button 
                                onClick={closeViewer}
                                className="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                            >
                                Cerrar visor
                            </button>
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