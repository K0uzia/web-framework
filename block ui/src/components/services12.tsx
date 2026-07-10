"use client";

import { motion } from "framer-motion";
import { ArrowUpRight } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { cn } from "@/lib/utils";

type ServiceProps = {
  title: string;
  image: string;
  url: string;
};

const services: ServiceProps[] = [
  {
    title: "Web Development",
    image:
      "https://deifkwefumgah.cloudfront.net/shadcnblocks/block/photos/simone-hutsch-6jEVl7xPH3E-unsplash.jpg",
    url: "",
  },
  {
    title: "Mobile App Development",
    image:
      "https://deifkwefumgah.cloudfront.net/shadcnblocks/block/photos/simone-hutsch-gDmVqxZt1hg-unsplash.jpg",
    url: "",
  },
  {
    title: "UI/UX Design",
    image:
      "https://deifkwefumgah.cloudfront.net/shadcnblocks/block/photos/simone-hutsch-9__Q24sJqKg-unsplash.jpg",
    url: "",
  },
  {
    title: "Digital Marketing",
    image:
      "https://deifkwefumgah.cloudfront.net/shadcnblocks/block/photos/simone-hutsch-duxeKbu9FDE-unsplash.jpg",
    url: "",
  },
  {
    title: "Cloud Solutions",
    image:
      "https://deifkwefumgah.cloudfront.net/shadcnblocks/block/photos/simone-hutsch-5oYbG-sEImY-unsplash.jpg",
    url: "",
  },
];

interface Services12Props {
  className?: string;
}

const Services12 = ({ className }: Services12Props) => {
  return (
    <section className={cn("py-32", className)}>
      <div className="container grid grid-cols-1 gap-10 lg:grid-cols-3">
        <div className="flex flex-col justify-between lg:col-span-1">
          <div>
            <h2 className="mb-4 text-4xl font-medium text-foreground md:text-6xl">
              Featured Services
            </h2>
            <p className="w-72 text-base tracking-tight text-muted-foreground">
              We offer comprehensive digital solutions to help your business
              grow. From web development to mobile apps, we deliver quality
              results that exceed expectations.
            </p>
          </div>
          <Button variant="outline" className="mt-8 w-fit">
            View all services <ArrowUpRight className="ml-2 h-4 w-4" />
          </Button>
        </div>

        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:col-span-2">
          {/* Featured Services - First 2 */}
          {services.slice(0, 2).map((service, idx) => (
            <motion.a
              key={idx}
              href={service.url}
              whileHover={{ opacity: 0.8 }}
              className="group block overflow-hidden rounded-xl"
            >
              <Card className="relative aspect-[3/4] overflow-hidden p-0">
                <img
                  src={service.image}
                  alt={service.title}
                  className="absolute inset-0 h-full w-full object-cover"
                />
                <CardContent className="absolute inset-0 flex flex-col justify-start p-6">
                  <div className="pr-4 font-semibold text-white">
                    {service.title}
                  </div>
                </CardContent>
                <ArrowUpRight className="absolute top-6 right-6 h-6 w-6 text-white transition-transform group-hover:scale-110" />
              </Card>
            </motion.a>
          ))}

          {/* Secondary Services - Remaining 3 */}
          <div className="col-span-full grid grid-cols-1 gap-4 sm:grid-cols-3">
            {services.slice(2).map((service, idx) => (
              <motion.a
                key={idx + 2}
                href={service.url}
                whileHover={{ opacity: 0.8 }}
                className="group block overflow-hidden rounded-xl"
              >
                <Card className="relative aspect-[4/3] overflow-hidden p-0">
                  <img
                    src={service.image}
                    alt={service.title}
                    className="absolute inset-0 h-full w-full object-cover"
                  />
                  <CardContent className="absolute inset-0 flex flex-col justify-start p-4">
                    <div className="pr-4 text-sm font-semibold text-white">
                      {service.title}
                    </div>
                  </CardContent>
                  <ArrowUpRight className="absolute top-4 right-4 h-5 w-5 text-white transition-transform group-hover:scale-110" />
                </Card>
              </motion.a>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

export { Services12 };
